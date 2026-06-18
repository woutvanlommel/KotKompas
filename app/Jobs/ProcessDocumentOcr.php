<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProcessDocumentOcr implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Document $document) {}

    public function handle(): void
    {
        $media = $this->document->getFirstMedia('document');
        $path = $media?->getPath();

        if ($path === null || ! is_file($path)) {
            return;
        }

        $this->document->update(['ocr_status' => Document::OCR_PROCESSING]);

        try {
            $response = Http::asMultipart()
                ->timeout((int) config('ocr-space.timeout'))
                ->withHeaders(['apikey' => config('ocr-space.api_key')])
                ->post(config('ocr-space.api_url'), [
                    ['name' => 'file', 'contents' => fopen($path, 'r')],
                    ['name' => 'filetype', 'contents' => $this->ocrFileType($media)],
                ]);

            $data = $response->json();

            // OCR.Space gooit IsErroredOnProcessing=true bij de page-limit van de
            // free tier (max 3 pagina's), maar levert dan toch de eerste pagina's
            // bruikbare tekst aan. Die gedeeltelijke tekst willen we behouden in
            // plaats van weg te gooien.
            $text = trim(collect($data['ParsedResults'] ?? [])
                ->map(fn (array $result) => $result['ParsedText'] ?? '')
                ->implode("\n"));

            if ($text === '') {
                Log::warning('ProcessDocumentOcr failed', [
                    'document_id' => $this->document->id,
                    'error' => $data['ErrorMessage'][0] ?? 'No parsed text returned by OCR.Space.',
                ]);

                $this->document->update(['ocr_status' => Document::OCR_FAILED]);

                return;
            }

            $this->document->update(['ocr_text' => $text]);

            $description = $this->generateDescription($text);

            if ($description !== null && $description !== '') {
                $this->document->update(['description' => $description]);
            }

            $this->document->update(['ocr_status' => Document::OCR_DONE]);
        } catch (\Throwable $e) {
            Log::warning('ProcessDocumentOcr failed', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);

            $this->document->update(['ocr_status' => Document::OCR_FAILED]);
        }
    }

    private function ocrFileType(?Media $media): string
    {
        return match ($media?->mime_type) {
            'application/pdf' => 'PDF',
            'image/png' => 'PNG',
            'image/jpeg' => 'JPG',
            'image/webp' => 'WEBP',
            default => 'PDF',
        };
    }

    private function generateDescription(string $text): ?string
    {
        $prompt = "Beschrijf in 2 a 3 zinnen in het Nederlands wat voor document dit is, op basis van de onderstaande OCR-tekst. Geef enkel de beschrijving terug, zonder inleiding.\n\n"
            .Str::limit($text, 3000, '');

        $model = config('services.gemini.model');

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.config('services.gemini.key'),
            [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]
        );

        $description = $response->json('candidates.0.content.parts.0.text');

        return $description !== null ? trim($description) : null;
    }
}
