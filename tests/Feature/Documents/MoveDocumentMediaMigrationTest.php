<?php

namespace Tests\Feature\Documents;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MoveDocumentMediaMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_document_media_is_moved_to_local(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        // Simulate a legacy doc whose media still lives on the public disk.
        $owner = User::factory()->create();
        $doc = $owner->documents()->create(['name' => 'Oud', 'type' => 'other']);
        $media = $doc->addMedia(UploadedFile::fake()->createWithContent('old.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        )))
            ->toMediaCollection('document', 'public');

        $this->assertSame('public', $media->refresh()->disk);
        Storage::disk('public')->assertExists("{$media->id}/old.png");

        // RefreshDatabase already ran every migration (including this one) before the
        // test body executed, so the migrator considers it "ran". Clear that record so
        // re-invoking it here actually executes the up() method against our fixture data.
        DB::table('migrations')->where('migration', '2026_06_19_130000_move_document_media_to_private_disk')->delete();

        // Re-run only the move migration.
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_06_19_130000_move_document_media_to_private_disk.php', '--force' => true]);

        $media->refresh();
        $this->assertSame('local', $media->disk);
        Storage::disk('local')->assertExists("{$media->id}/old.png");
    }
}
