<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Send email — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Send email</h1>
    <form method="POST" action="{{ route('admin.send-emails.store') }}">
        @csrf
        <label>Owner
            <select name="owner_id" required>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}">{{ $owner->full_name }} ({{ $owner->owner_email }})</option>
                @endforeach
            </select>
        </label>
        <label>Template
            <select name="template_id">
                <option value="">—</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Document
            <select name="document_id">
                <option value="">—</option>
                @foreach ($documents as $document)
                    <option value="{{ $document->id }}">{{ $document->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Subject <input name="subject"></label>
        <label>Body <textarea name="body"></textarea></label>
        <button type="submit">Send</button>
    </form>
</body>
</html>
