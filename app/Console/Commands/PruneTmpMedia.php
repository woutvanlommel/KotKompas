<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneTmpMedia extends Command
{
    protected $signature = 'app:prune-tmp-media
                            {--hours=24 : Delete files older than this many hours}
                            {--dry-run  : List what would be deleted without deleting}';

    protected $description = 'Delete orphaned tmp-media uploads older than the given threshold.';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subHours($hours)->timestamp;

        $deletedFiles = 0;
        $deletedDirs = 0;

        foreach ($disk->allFiles('tmp-media') as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                if ($dryRun) {
                    $this->line("[dry-run] would delete: {$file}");
                } else {
                    $disk->delete($file);
                }
                $deletedFiles++;
            }
        }

        // Delete empty directories — reversed so deepest paths come first
        $dirs = array_reverse($disk->allDirectories('tmp-media'));

        foreach ($dirs as $dir) {
            if (empty($disk->allFiles($dir))) {
                if ($dryRun) {
                    $this->line("[dry-run] would remove empty dir: {$dir}");
                } else {
                    $disk->deleteDirectory($dir);
                }
                $deletedDirs++;
            }
        }

        $this->info(
            $dryRun
                ? "Dry run: {$deletedFiles} file(s) and {$deletedDirs} dir(s) would be removed."
                : "Pruned {$deletedFiles} file(s) and {$deletedDirs} empty dir(s) from tmp-media."
        );

        return self::SUCCESS;
    }
}
