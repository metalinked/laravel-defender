<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $status }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: system-ui, sans-serif; background: #f7f7f8; color: #222; }
        main { text-align: center; padding: 1rem; }
        h1 { font-size: 3rem; margin: 0 0 .5rem; color: #888; }
        @media (prefers-color-scheme: dark) { body { background: #111; color: #ddd; } }
    </style>
</head>
<body>
    <main>
        <h1>{{ $status }}</h1>
        <p>{{ $message }}</p>
    </main>
</body>
</html>
