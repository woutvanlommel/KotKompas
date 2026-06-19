<?php

namespace Tests\Feature\Documents;

use App\Enums\DocumentVisibility;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function docWithFile(User $owner, DocumentVisibility $visibility, ?int $sharedWith = null): Document
    {
        $doc = $owner->documents()->create([
            'name' => 'Bestand', 'type' => 'other',
            'visibility' => $visibility, 'shared_with_user_id' => $sharedWith,
        ]);
        $doc->addMedia(
            UploadedFile::fake()->createWithContent('f.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
            ))
        )->toMediaCollection('document');

        return $doc;
    }

    public function test_owner_can_download_stranger_cannot(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $recipient = User::factory()->create();
        $stranger = User::factory()->create();

        $doc = $this->docWithFile($owner, DocumentVisibility::User, $recipient->id);

        $this->actingAs($owner)->get(route('documents.download', $doc))->assertOk();
        $this->actingAs($recipient)->get(route('documents.download', $doc))->assertOk();
        $this->actingAs($stranger)->get(route('documents.download', $doc))->assertForbidden();
    }

    public function test_guests_are_redirected(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $doc = $this->docWithFile($owner, DocumentVisibility::Private);

        $this->get(route('documents.download', $doc))->assertRedirect();
    }
}
