<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Property payments — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Property payments</h1>
    <ul>
        @foreach ($payments as $payment)
            <li>#{{ $payment->id }} — property {{ $payment->property_id }} — {{ $payment->amount }}</li>
        @endforeach
    </ul>
</body>
</html>
