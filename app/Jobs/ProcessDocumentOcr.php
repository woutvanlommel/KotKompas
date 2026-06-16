<?php

namespace App\Jobs;

use App\Models\Document;
use Codesmiths\LaravelOcrSpace\Facades\OcrSpace;
use Codesmiths\LaravelOcrSpace\OcrSpaceOptions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessDocumentOcr implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Document $document) {}

    public function handle(): void
    {
        $path = $this->document->getFirstMedia('document')?->getPath();

        if ($path === null || ! is_file($path)) {
            return;
        }

        try {
            $response = OcrSpace::parseImageFile($path, OcrSpaceOptions::make());
            $text = $response->getParsedResults()->first()?->getParsedText() ?? '';

            $this->document->update(['ocr_text' => $text]);

            if (trim($text) === '') {
                return;
            }

            $description = $this->generateDescription($text);

            if ($description !== null && $description !== '') {
                $this->document->update(['description' => $description]);
            }
        } catch (\Throwable $e) {
            Log::warning('ProcessDocumentOcr failed', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateDescription(string $text): ?string
    {
        $prompt = "Beschrijf in 2 a 3 zinnen in het Nederlands wat voor document dit is, op basis van de onderstaande OCR-tekst. Geef enkel de beschrijving terug, zonder inleiding.\n\n"
            . Str::limit($text, 3000, '');

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . config('services.gemini.key'),
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
