<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bookings — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Bookings</h1>
    <ul>
        @foreach ($bookings as $booking)
            <li>
                <a href="{{ route('admin.bookings.show', $booking) }}">
                    {{ $booking->booking_code ?? ('#'.$booking->id) }}
                </a>
                @if ($booking->isCancelled())
                    (cancelled)
                @endif
            </li>
        @endforeach
    </ul>
</body>
</html>
