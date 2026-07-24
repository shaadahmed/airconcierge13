<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;

class FailedJobController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('accessSuperAdminArea', User::class);

        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(25)
            ->through(function (stdClass $job): stdClass {
                $payload = json_decode($job->payload, true);
                $job->display_name = is_array($payload)
                    ? ($payload['displayName'] ?? $payload['job'] ?? 'Unknown')
                    : 'Unknown';

                return $job;
            });

        return view('admin.failed-jobs.index', [
            'failedJobs' => $failedJobs,
        ]);
    }

    public function retry(string $uuid): RedirectResponse
    {
        $this->authorize('accessSuperAdminArea', User::class);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return redirect()
            ->route('admin.failed-jobs.index')
            ->with('status', "Retry queued for failed job {$uuid}.");
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $this->authorize('accessSuperAdminArea', User::class);

        Artisan::call('queue:forget', ['id' => $uuid]);

        return redirect()
            ->route('admin.failed-jobs.index')
            ->with('status', "Forgot failed job {$uuid}.");
    }

    public function retryAll(): RedirectResponse
    {
        $this->authorize('accessSuperAdminArea', User::class);

        Artisan::call('queue:retry', ['id' => ['all']]);

        return redirect()
            ->route('admin.failed-jobs.index')
            ->with('status', 'Retry queued for all failed jobs.');
    }
}
