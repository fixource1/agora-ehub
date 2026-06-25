<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DownloadStatus;
use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'resource_file_id' => ['nullable', 'exists:resource_files,id'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $download = Download::query()->updateOrCreate(
            [
                'user_id' => $request->user()?->id,
                'resource_id' => $validated['resource_id'],
                'device_id' => $validated['device_id'] ?? $request->header('X-Device-Id'),
            ],
            [
                'resource_file_id' => $validated['resource_file_id'] ?? null,
                'status' => DownloadStatus::Downloaded,
                'device_name' => $validated['device_name'] ?? $request->header('X-Device-Name', 'Web'),
                'downloaded_at' => now(),
                'last_synced_at' => now(),
            ],
        );

        Resource::query()
            ->whereKey($validated['resource_id'])
            ->increment('download_count');

        return response()->json([
            'data' => $download->load('resource'),
            'message' => 'Resource marked for offline access.',
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $downloads = Download::query()
            ->with(['resource.resourceType', 'resource.files'])
            ->when($request->user(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->where('status', DownloadStatus::Downloaded)
            ->latest('downloaded_at')
            ->get();

        return response()->json(['data' => $downloads]);
    }
}
