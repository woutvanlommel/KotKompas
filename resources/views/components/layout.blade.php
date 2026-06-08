@props([
    'title' => config('app.name', 'KotKompas'),
    'bodyClass' => 'bg-base-een-200 text-primary-900',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="stylesheet" href="https://use.typekit.net/ztn2kjh.css">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    {{ $head ?? '' }}
</head>
<body {{ $attributes->class(['min-h-screen font-sans antialiased', $bodyClass]) }}>
    {{ $slot }}
</body>
</html>
