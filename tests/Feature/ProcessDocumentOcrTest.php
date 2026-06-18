<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentOcr;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessDocumentOcrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function createDocumentWithMedia(): Document
    {
        $user = User::factory()->create();

        $document = $user->documents()->create([
            'name' => 'Test document',
            'type' => 'other',
        ]);

        $tmpPath = tempnam(sys_get_temp_dir(), 'ocr').'.png';
        // Minimal valid 1x1 PNG bytes.
        file_put_contents($tmpPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));

        $document->addMedia($tmpPath)
            ->preservingOriginal()
            ->toMediaCollection('document');

        @unlink($tmpPath);

        $this->assertNotNull($document->getFirstMedia('document'));

        return $document;
    }

    public function test_happy_path_stores_text_description_and_marks_done(): void
    {
        $document = $this->createDocumentWithMedia();

        Http::fake([
            config('ocr-space.api_url') => Http::response([
                'IsErroredOnProcessing' => false,
                'ParsedResults' => [
                    ['ParsedText' => 'Some extracted text'],
                ],
            ]),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Een Nederlandse beschrijving.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        (new ProcessDocumentOcr($document))->handle();

        $document->refresh();

        $this->assertStringContainsString('Some extracted text', $document->ocr_text);
        $this->assertSame('Een Nederlandse beschrijving.', $document->description);
        $this->assertSame(Document::OCR_DONE, $document->ocr_status);
    }

    public function test_page_limit_partial_result_keeps_all_parsed_pages(): void
    {
        $document = $this->createDocumentWithMedia();

        Http::fake([
            config('ocr-space.api_url') => Http::response([
                'IsErroredOnProcessing' => true,
                'OCRExitCode' => 4,
                'ErrorMessage' => ['The maximum page limit of 3 was reached. Please upgrade for more.'],
                'ParsedResults' => [
                    ['ParsedText' => 'Page one text'],
                    ['ParsedText' => 'Page two text'],
                    ['ParsedText' => 'Page three text'],
                ],
            ]),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Beschrijving van een meerpagina document.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        (new ProcessDocumentOcr($document))->handle();

        $document->refresh();

        $this->assertStringContainsString('Page one text', $document->ocr_text);
        $this->assertStringContainsString('Page two text', $document->ocr_text);
        $this->assertStringContainsString('Page three text', $document->ocr_text);
        $this->assertSame('Beschrijving van een meerpagina document.', $document->description);
        $this->assertSame(Document::OCR_DONE, $document->ocr_status);
    }

    public function test_hard_failure_with_no_parsed_text_marks_failed_and_skips_gemini(): void
    {
        $document = $this->createDocumentWithMedia();

        Http::fake([
            config('ocr-space.api_url') => Http::response([
                'IsErroredOnProcessing' => true,
                'OCRExitCode' => 3,
                'ErrorMessage' => ['Unable to process the uploaded file.'],
                'ParsedResults' => [],
            ]),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Dit zou niet aangeroepen mogen worden.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        (new ProcessDocumentOcr($document))->handle();

        $document->refresh();

        $this->assertEmpty($document->ocr_text);
        $this->assertNull($document->description);
        $this->assertSame(Document::OCR_FAILED, $document->ocr_status);

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'generativelanguage.googleapis.com'));
    }

    public function test_no_media_returns_early_and_leaves_status_unchanged(): void
    {
        $user = User::factory()->create();

        $document = $user->documents()->create([
            'name' => 'Document zonder bestand',
            'type' => 'other',
        ]);

        Http::fake();

        (new ProcessDocumentOcr($document))->handle();

        $document->refresh();

        $this->assertNull($document->ocr_status);
        $this->assertNull($document->ocr_text);

        Http::assertNothingSent();
    }
}
