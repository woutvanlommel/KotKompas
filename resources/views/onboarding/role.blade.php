<x-layout title="Kies je rol · KotKompas" body-class="bg-primary-900 text-white overflow-x-hidden">
<div class="relative min-h-dvh">

    {{-- Same hero + navy scrim as the auth pages --}}
    <div class="fixed inset-0 z-0" aria-hidden="true">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('img/hero-test.jpg') }}');"></div>
        <div class="absolute inset-0 bg-linear-to-tr from-primary-900 via-primary-900/75 to-primary-900/20"></div>
    </div>

    <header class="fixed inset-x-0 top-0 z-10 px-[clamp(1.5rem,4vw,3.5rem)] py-[clamp(1rem,2.25vw,1rem)]">
        <img src="{{ asset('/img/400pxX100pxWoordLogoLiggendZwart.png') }}" alt="KotKompas" class="h-18 w-auto [filter:brightness(0)_invert(1)]">
    </header>

    <div class="relative z-[1] flex min-h-dvh items-center justify-center px-5 py-24">
        <div class="w-full max-w-xl">
            <header class="mb-8 text-center">
                <h1 class="text-3xl font-medium tracking-tight sm:text-4xl">Welkom bij KotKompas</h1>
                <p class="mt-3 text-base text-white/75">Wat brengt jou hier? Je kan dit later niet zomaar wijzigen.</p>
            </header>

            @error('role')
                <p class="mb-4 text-center text-sm text-accent-300">{{ $message }}</p>
            @enderror

            <div class="grid gap-4 sm:grid-cols-2">
                @php
                    $roles = [
                        'huurder' => ['title' => 'Ik zoek een kot', 'sub' => 'Huurder (student)', 'desc' => 'Zoek, vergelijk en plan bezichtigingen.'],
                        'verhuurder' => ['title' => 'Ik verhuur', 'sub' => 'Verhuurder (eigenaar)', 'desc' => 'Plaats en beheer je panden.'],
                    ];
                @endphp

                @foreach ($roles as $value => $role)
                    <form method="POST" action="{{ route('onboarding.role.store') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ $value }}">
                        <button type="submit"
                            class="group flex h-full w-full flex-col items-start rounded-[10px] border border-white/22 bg-white/10 p-6 text-left backdrop-blur-[14px] transition hover:border-white/50 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/40">
                            <span class="text-lg font-semibold">{{ $role['title'] }}</span>
                            <span class="mt-1 text-xs font-semibold uppercase tracking-[0.12em] text-accent-400">{{ $role['sub'] }}</span>
                            <span class="mt-3 text-sm leading-relaxed text-white/70">{{ $role['desc'] }}</span>
                            <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-white/80 group-hover:text-white">
                                Kies
                                <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none"><path d="M3 13L13 3M13 3H5M13 3V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</div>
</x-layout>
