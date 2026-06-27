<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\ResourceFile;
use Database\Seeders\Support\SampleResourceFiles;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceFileDownloadController extends Controller
{
    public function __invoke(Resource $resource, ResourceFile $resourceFile): StreamedResponse
    {
        abort_unless($resource->status === 'published', 404);
        abort_unless($resourceFile->resource_id === $resource->id, 404);
        abort_unless($resourceFile->is_downloadable, 403);

        if (config('agora.performance.ensure_sample_files_on_download', false)) {
            SampleResourceFiles::ensureForFile($resourceFile);
        }

        $disk = Storage::disk($resourceFile->disk);

        abort_unless($disk->exists($resourceFile->file_path), 404);

        return $disk->download(
            $resourceFile->file_path,
            $resourceFile->file_name,
            [
                'Content-Type' => $resourceFile->mime_type ?? 'application/octet-stream',
                'Cache-Control' => 'private, max-age=3600',
            ],
        );
    }
}
