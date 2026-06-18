<?php

namespace Tests\Feature;

use App\Filament\Dashboard\Pages\Documents;
use App\Jobs\ProcessDocumentOcr;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('dashboard');
    }

    public function test_uploading_a_document_dispatches_ocr_job_and_sets_pending_status(): void
    {
        Storage::fake('public');
        Queue::fake();

        $tenant = User::factory()->create();
        $tenant->assignRole('huurder');

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        Livewire::actingAs($tenant)
            ->test(Documents::class)
            ->callAction('upload', data: [
                'name' => 'Inschrijvingsbewijs',
                'file' => $file,
                'type' => 'school',
                'is_public' => false,
            ]);

        $document = Document::where('user_id', $tenant->id)->latest()->firstOrFail();

        $this->assertSame(Document::OCR_PENDING, $document->ocr_status);

        Queue::assertPushed(ProcessDocumentOcr::class, fn (ProcessDocumentOcr $job) => $job->document->is($document));
    }
}
