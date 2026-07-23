<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $chronology->name }} — {{ config('app.name') }}</title>
</head>
<body>
    <h1>{{ $chronology->name }}</h1>
    <p>Start: {{ $chronology->startdate?->toDateString() }}</p>
    <p><a href="{{ route('admin.chronologies.edit', $chronology) }}">Edit</a></p>
    <h2>Orders</h2>
    <ul>
        @foreach ($chronology->orders as $order)
            <li>Day {{ $order->day }} at {{ $order->time }}:{{ $order->minute }}</li>
        @endforeach
    </ul>
</body>
</html>
