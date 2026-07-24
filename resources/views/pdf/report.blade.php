<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report — {{ $reportType }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h1>Report: {{ $reportType }}</h1>
    <p>Generated at {{ now()->toDateTimeString() }}</p>
    <table>
        <thead>
            <tr>
                @foreach (($rows->first()?->getAttributes() ?? (array) $rows->first() ?? []) as $key => $value)
                    <th>{{ $key }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ((array) (method_exists($row, 'getAttributes') ? $row->getAttributes() : (array) $row) as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
