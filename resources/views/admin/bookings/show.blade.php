<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking {{ $booking->booking_code ?? $booking->id }} — {{ config('app.name') }}</title>
</head>
<body>
    <p><a href="{{ route('admin.bookings.index') }}">Back</a></p>
    <h1>Booking {{ $booking->booking_code ?? ('#'.$booking->id) }}</h1>
    <p>Property: {{ $booking->property?->property_title }}</p>
    <p>Guests: {{ $booking->no_of_guests }}</p>
    <p>Dates: {{ $booking->reservation_start_date?->toDateString() }} → {{ $booking->reservation_end_date?->toDateString() }}</p>
    <p>Status: {{ $booking->isCancelled() ? 'Cancelled' : 'Active' }}</p>
</body>
</html>
