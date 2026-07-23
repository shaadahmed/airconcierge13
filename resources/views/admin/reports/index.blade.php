<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reports — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Reports</h1>
    <ul>
        @foreach ($summary as $row)
            <li>Property {{ $row->property_id }} — {{ $row->booking_count }} bookings</li>
        @endforeach
    </ul>
</body>
</html>
