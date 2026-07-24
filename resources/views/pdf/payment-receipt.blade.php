<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment receipt #{{ $payment->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; }
    </style>
</head>
<body>
    <h1>Payment receipt</h1>
    <p>Payment ID: {{ $payment->id }}</p>
    <p>Booking ID: {{ $payment->booking_id }}</p>
    <p>Amount: {{ $payment->amount }}</p>
    <p>Payment date: {{ optional($payment->payment_date)->toDateString() }}</p>
    <p>Notes: {{ $payment->notes }}</p>
</body>
</html>
