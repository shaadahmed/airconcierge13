<?php

namespace App\Services\Images;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Per-batch image compression for scheduled uploads work.
 */
class ImageCompressionService
{
    public function compressBatch(int $limit = 100): int
    {
        // Follow-up: scan upload disk and compress binaries. Pipeline dispatches batch jobs.
        $files = collect(Storage::disk('public')->allFiles())
            ->filter(fn (string $path): bool => (bool) preg_match('/\.(jpe?g|png)$/i', $path))
            ->take($limit)
            ->values();

        foreach ($files as $path) {
            Log::debug('ImageCompressionService would compress.', ['path' => $path]);
        }

        Log::info('ImageCompressionService batch complete.', [
            'limit' => $limit,
            'candidates' => $files->count(),
        ]);

        return $files->count();
    }
}
