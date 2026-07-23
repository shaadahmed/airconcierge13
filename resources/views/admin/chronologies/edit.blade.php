<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit chronology — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Edit chronology</h1>
    <form method="POST" action="{{ route('admin.chronologies.update', $chronology) }}">
        @csrf
        @method('PUT')
        <label>Name <input name="name" value="{{ old('name', $chronology->name) }}" required></label>
        <label>Start date <input type="date" name="startdate" value="{{ old('startdate', $chronology->startdate?->toDateString()) }}" required></label>
        <button type="submit">Update</button>
    </form>
</body>
</html>
