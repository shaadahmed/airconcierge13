<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — {{ config('app.name', 'Air Concierge') }}</title>
</head>
<body>
    <h1>Admin dashboard</h1>
    <p>Signed in as {{ auth()->user()?->email }}</p>
    <ul>
        <li>Bookings this month: {{ $stats['bookings_this_month'] ?? 0 }}</li>
        <li>Active properties: {{ $stats['active_properties'] ?? 0 }}</li>
        <li>Owners: {{ $stats['owners'] ?? 0 }}</li>
        <li>Revenue this month: {{ $stats['revenue_this_month'] ?? 0 }}</li>
    </ul>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log out</button>
    </form>
</body>
</html>
