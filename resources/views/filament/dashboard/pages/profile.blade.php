<x-filament-panels::page>
    @php
        $user = auth()->user();
        $roleLabel = $user->hasRole('verhuurder') ? 'Verhuurder' : ($user->hasRole('huurder') ? 'Huurder' : 'Gebruiker');
        $verified = $user->email_verified_at !== null;
        $isLandlord = $user->hasRole('verhuurder');
        $hasScore = $isLandlord && $user->landlord_score !== null && $user->landlord_reviews_count > 0;
    @endphp

    <div class="grid gap-6 lg:grid-cols-[minmax(0,21rem)_1fr] lg:items-start">

        {{-- Identiteit / account-samenvatting --}}
        <aside class="h-fit rounded-[1.25rem] border border-[#0f17201f] bg-white p-6 sm:p-8 lg:sticky lg:top-6">
            <div class="flex flex-col items-center text-center">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                         class="h-24 w-24 rounded-full object-cover ring-1 ring-[#0f17201f]" />
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[#edf0f4] text-3xl font-medium tracking-[-0.02em] text-[#586573]">
                        {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->lastname, 0, 1)) }}
                    </div>
                @endif

                <p class="mt-5 text-xl font-medium tracking-[-0.02em] text-[#0f1720]">{{ $user->full_name }}</p>
                <span class="mt-2 inline-flex items-center rounded-md bg-[#edf0f4] px-2.5 py-0.5 text-[0.625rem] font-semibold uppercase tracking-[0.12em] text-[#586573]">{{ $roleLabel }}</span>
                <p class="mt-3 truncate text-sm tracking-[-0.01em] text-[#586573]">{{ $user->email }}</p>
            </div>

            <dl class="mt-6 space-y-3 border-t border-[#0f17201f] pt-6">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Lid sinds</dt>
                    <dd class="text-sm font-medium tracking-[-0.01em] text-[#0f1720]">{{ $user->created_at?->translatedFormat('F Y') ?? '—' }}</dd>
                </div>
                @if ($hasScore)
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">Verhuurderscore</dt>
                        <dd class="inline-flex items-baseline gap-1 text-sm font-medium tabular-nums text-[#0f1720]">
                            <span class="text-[#caa12a]" aria-hidden="true">&starf;</span>
                            {{ \App\Support\Score::format($user->landlord_score) }}
                            <span class="text-xs font-normal text-[#586573]">({{ $user->landlord_reviews_count }})</span>
                        </dd>
                    </div>
                @endif
            </dl>
        </aside>

        {{-- Gegevens --}}
        <section class="rounded-[1.25rem] border border-[#0f17201f] bg-white p-6 sm:p-8">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">Persoonsgegevens</p>

            <dl class="mt-5 divide-y divide-[#0f17201f] border-t border-[#0f17201f]">
                @php
                    $rows = [
                        ['label' => 'Voornaam', 'value' => $user->name],
                        ['label' => 'Achternaam', 'value' => $user->lastname],
                        ['label' => 'Geboortedatum', 'value' => $user->date_of_birth?->format('d/m/Y')],
                        ['label' => 'Telefoonnummer', 'value' => $user->phone],
                    ];
                @endphp
                @foreach ($rows as $row)
                    <div class="flex items-baseline justify-between gap-6 py-4">
                        <dt class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">{{ $row['label'] }}</dt>
                        <dd class="min-w-0 truncate text-sm font-medium tracking-[-0.01em] {{ $row['value'] ? 'text-[#0f1720]' : 'text-[#9aa6b4]' }}">
                            {{ $row['value'] ?: 'Niet ingevuld' }}
                        </dd>
                    </div>
                @endforeach

                {{-- E-mail met geverifieerd-status --}}
                <div class="flex items-baseline justify-between gap-6 py-4">
                    <dt class="shrink-0 text-[0.625rem] font-semibold uppercase tracking-[0.14em] text-[#586573]">E-mailadres</dt>
                    <dd class="flex min-w-0 items-center gap-2">
                        <span class="truncate text-sm font-medium tracking-[-0.01em] text-[#0f1720]">{{ $user->email }}</span>
                        @if ($verified)
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-[#e7f6ec] px-2 py-0.5 text-[0.625rem] font-semibold uppercase tracking-[0.1em] text-[#15803d]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#15803d]"></span>
                                Geverifieerd
                            </span>
                        @else
                            <span class="inline-flex shrink-0 items-center rounded-md bg-[#edf0f4] px-2 py-0.5 text-[0.625rem] font-semibold uppercase tracking-[0.1em] text-[#586573]">Niet geverifieerd</span>
                        @endif
                    </dd>
                </div>
            </dl>

            <p class="mt-6 border-t border-[#0f17201f] pt-5 text-xs tracking-[-0.01em] text-[#586573]">
                Naam staat vast. E-mail, telefoon en je profielfoto pas je aan via <span class="font-medium text-[#0f1720]">Profiel bewerken</span> bovenaan.
            </p>
        </section>
    </div>
</x-filament-panels::page>
