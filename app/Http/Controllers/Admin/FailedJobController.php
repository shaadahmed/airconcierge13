<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class FailedJobController extends Controller
{
    public function index(): JsonResponse
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(25)
            ->through(function (stdClass $job): array {
                $payload = json_decode($job->payload, true);

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'failed_at' => $job->failed_at,
                    'display_name' => is_array($payload)
                        ? ($payload['displayName'] ?? $payload['job'] ?? 'Unknown')
                        : 'Unknown',
                    'exception' => Str::limit((string) $job->exception, 500),
                ];
            });

        return response()->json($failedJobs);
    }

    public function retry(string $uuid): JsonResponse
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return response()->json([
            'status' => "Retry queued for failed job {$uuid}.",
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        return response()->json([
            'status' => "Forgot failed job {$uuid}.",
        ]);
    }

    public function retryAll(): JsonResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return response()->json([
            'status' => 'Retry queued for all failed jobs.',
        ]);
    }
}
