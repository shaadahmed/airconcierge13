<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Chronologies — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Chronologies</h1>
    <p><a href="{{ route('admin.chronologies.create') }}">Create</a></p>
    <ul>
        @foreach ($chronologies as $chronology)
            <li>
                <a href="{{ route('admin.chronologies.show', $chronology) }}">{{ $chronology->name }}</a>
            </li>
        @endforeach
    </ul>
</body>
</html>
