<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Zoho Sign — {{ config('app.name') }}</title>
</head>
<body>
    <h1>Zoho Sign</h1>
    @if ($token)
        <p>Token configured. Last updated: {{ $token->update_dtm }}</p>
        <p>Expires soon: {{ $token->isExpired() ? 'yes' : 'no' }}</p>
    @else
        <p>No Zoho token row in zoho_code_details.</p>
    @endif
</body>
</html>
