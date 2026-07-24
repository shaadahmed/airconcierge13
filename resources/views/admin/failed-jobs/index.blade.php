<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Failed jobs — {{ config('app.name', 'Air Concierge') }}</title>
</head>
<body>
    <h1>Failed jobs</h1>
    <p>Superadmin retry dashboard (Phase 4 — no Horizon).</p>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.failed-jobs.retry-all') }}">
        @csrf
        <button type="submit">Retry all</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Job</th>
                <th>Queue</th>
                <th>Failed at</th>
                <th>Exception</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($failedJobs as $job)
                <tr>
                    <td>{{ $job->uuid }}</td>
                    <td>{{ $job->display_name }}</td>
                    <td>{{ $job->queue }}</td>
                    <td>{{ $job->failed_at }}</td>
                    <td><pre>{{ \Illuminate\Support\Str::limit($job->exception, 200) }}</pre></td>
                    <td>
                        <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->uuid) }}" style="display:inline">
                            @csrf
                            <button type="submit">Retry</button>
                        </form>
                        <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->uuid) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Forget</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No failed jobs.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $failedJobs->links() }}
</body>
</html>
