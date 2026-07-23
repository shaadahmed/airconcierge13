<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create chronology — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Create chronology</h1>
    <form method="POST" action="{{ route('admin.chronologies.store') }}">
        @csrf
        <label>Name <input name="name" value="{{ old('name') }}" required></label>
        <label>Start date <input type="date" name="startdate" value="{{ old('startdate') }}" required></label>
        <label>Option
            <select name="chronologyoption">
                <option value="0">All in scope</option>
                <option value="1">Owners before start</option>
                <option value="2">Properties after start</option>
            </select>
        </label>
        <button type="submit">Save</button>
    </form>
</body>
</html>
