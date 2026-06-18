@props([
    'title' => config('app.name', 'KotKompas'),
    'description' => 'Vind je studentenkot rechtstreeks van de eigenaar — zonder makelaarskosten. Zoek, vergelijk en plan bezichtigingen in heel Vlaanderen.',
    'bodyClass' => 'bg-base-een-200 text-primary-900',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('img/favicon-256.png') }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ asset('img/hero-bg.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="stylesheet" href="https://use.typekit.net/ztn2kjh.css">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
<body {{ $attributes->class(['min-h-screen font-sans antialiased', $bodyClass]) }}>
    {{ $slot }}

    {{-- Livewire bundelen we zelf in app.ts (inject_assets=false); deze tag levert enkel de config. --}}
    @livewireScriptConfig
</body>
</html>
