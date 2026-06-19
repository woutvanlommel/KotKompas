@props([
    'title' => config('app.name', 'KotKompas'),
    'description' => 'Vind je studentenkot rechtstreeks van de eigenaar — zonder makelaarskosten. Zoek, vergelijk en plan bezichtigingen in heel Vlaanderen.',
    'bodyClass' => 'bg-base-een-200 text-primary-900',
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'robots' => null,
])
@php
    $canonicalUrl = $canonical ?? url()->current();
    $ogImageUrl = $ogImage ?: asset('img/hero-bg.jpg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @if ($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('img/favicon-256.png') }}">

    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="KotKompas">
    <meta property="og:locale" content="nl_BE">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImageUrl }}">

    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="stylesheet" href="https://use.typekit.net/ztn2kjh.css">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    {{ $head ?? '' }}
</head>
<body {{ $attributes->class(['min-h-screen font-sans antialiased', $bodyClass]) }}>
    {{ $slot }}
</body>
</html>
