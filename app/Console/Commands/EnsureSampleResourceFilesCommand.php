<?php

namespace App\Console\Commands;

use App\Models\ResourceFile;
use Database\Seeders\Support\SampleResourceFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class EnsureSampleResourceFilesCommand extends Command
{
    protected $signature = 'agora:ensure-sample-files';

    protected $description = 'Create placeholder sample resource files on disk when missing';

    public function handle(): int
    {
        $files = ResourceFile::query()
            ->where('file_path', 'like', 'resources/samples/%')
            ->get();

        $created = 0;

        foreach ($files as $file) {
            $existed = Storage::disk($file->disk)->exists($file->file_path);
            SampleResourceFiles::ensureForFile($file);

            if (! $existed) {
                $created++;
                $this->line("Created {$file->file_path}");
            }
        }

        $this->info("Sample files ready ({$created} created, {$files->count()} total).");

        return self::SUCCESS;
    }
}
