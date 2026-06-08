@extends('layouts.app')

@section('title', 'Contact · KotKompas')

@section('content')
<section class="mx-auto w-full max-w-xl px-5 py-12 sm:px-6 sm:py-20">

    {{-- Heading --}}
    <header class="mb-8 sm:mb-10">
        <p class="mb-3 text-sm font-semibold uppercase tracking-wider text-accent-500">Contact</p>
        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-primary-900 sm:text-4xl">
            Stuur ons een bericht
        </h1>
        <p class="mt-3 max-w-prose text-base leading-relaxed text-base-een-800">
            Vraag, feedback of samenwerking? Vul het formulier in — we antwoorden doorgaans binnen 24&nbsp;uur.
        </p>
    </header>

    {{-- Success flash (backend sets session('success')) --}}
    @if (session('success'))
        <div class="mb-6 rounded-md border border-primary-200 bg-primary-100 px-4 py-3 text-sm text-primary-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error flash (backend sets session('error') on send failure) --}}
    @if (session('error'))
        <div class="mb-6 rounded-md border border-accent-300 bg-accent-100 px-4 py-3 text-sm text-accent-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('contact.send') }}"
        data-contact-form
        class="space-y-5"
    >
        @csrf

        {{-- Honeypot: bots fill it, humans never see it. Backend rejects if non-empty. --}}
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
        </div>

        {{-- Naam --}}
        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-primary-900">
                Naam <span class="text-accent-500">*</span>
            </label>
            <input
                type="text"
                name="name"
                id="name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                @error('name') aria-invalid="true" @enderror
                class="block w-full rounded-md border border-base-twee-500 bg-white px-3.5 py-2.5 text-base text-primary-900 transition placeholder:text-base-twee-600 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
            >
            @error('name')
                <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-primary-900">
                E-mail <span class="text-accent-500">*</span>
            </label>
            <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                @error('email') aria-invalid="true" @enderror
                class="block w-full rounded-md border border-base-twee-500 bg-white px-3.5 py-2.5 text-base text-primary-900 transition placeholder:text-base-twee-600 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
            >
            @error('email')
                <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Onderwerp --}}
        <div>
            <label for="subject" class="mb-1.5 block text-sm font-medium text-primary-900">
                Onderwerp <span class="text-accent-500">*</span>
            </label>
            <select
                name="subject"
                id="subject"
                required
                @error('subject') aria-invalid="true" @enderror
                class="block w-full rounded-md border border-base-twee-500 bg-white px-3.5 py-2.5 text-base text-primary-900 transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
            >
                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Kies een onderwerp…</option>
                @foreach (['Algemene vraag', 'Feedback', 'Samenwerking', 'Probleem melden', 'Anders'] as $option)
                    <option value="{{ $option }}" @selected(old('subject') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @error('subject')
                <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Bericht --}}
        <div>
            <label for="message" class="mb-1.5 block text-sm font-medium text-primary-900">
                Bericht <span class="text-accent-500">*</span>
            </label>
            <textarea
                name="message"
                id="message"
                rows="6"
                required
                minlength="10"
                @error('message') aria-invalid="true" @enderror
                class="block w-full resize-y rounded-md border border-base-twee-500 bg-white px-3.5 py-2.5 text-base text-primary-900 transition placeholder:text-base-twee-600 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200"
            >{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- GDPR consent --}}
        <div>
            <label class="flex items-start gap-3 text-sm leading-relaxed text-base-een-800">
                <input
                    type="checkbox"
                    name="consent"
                    value="1"
                    required
                    @checked(old('consent'))
                    @error('consent') aria-invalid="true" @enderror
                    class="mt-0.5 h-4.5 w-4.5 shrink-0 rounded border-base-twee-500 text-primary-500 focus:ring-2 focus:ring-primary-200"
                >
                <span>
                    Ik ga akkoord dat mijn gegevens verwerkt worden om mijn bericht te beantwoorden.
                    <a href="/privacy" class="font-medium text-primary-600 underline underline-offset-2">Privacybeleid</a>.
                </span>
            </label>
            @error('consent')
                <p class="mt-1.5 text-sm text-accent-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            data-submit
            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-accent-500 px-5 py-3 text-base font-semibold text-white transition hover:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-300 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
        >
            <span data-label>Verstuur bericht</span>
            <svg data-spinner class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
        </button>
    </form>
</section>

{{-- Submitting state: disable button + show spinner on submit --}}
<script>
    document.querySelector('[data-contact-form]')?.addEventListener('submit', (e) => {
        const btn = e.currentTarget.querySelector('[data-submit]');
        if (!btn) return;
        btn.disabled = true;
        btn.querySelector('[data-label]').textContent = 'Versturen…';
        btn.querySelector('[data-spinner]').classList.remove('hidden');
    });
</script>
@endsection
