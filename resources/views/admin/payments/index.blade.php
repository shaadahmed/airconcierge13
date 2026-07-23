<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payments — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Booking payments</h1>
    <ul>
        @foreach ($payments as $payment)
            <li>#{{ $payment->id }} — booking {{ $payment->booking_id }} — {{ $payment->amount }}</li>
        @endforeach
    </ul>
</body>
</html>
