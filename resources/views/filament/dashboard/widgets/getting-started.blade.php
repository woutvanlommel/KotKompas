<x-filament-widgets::widget>
    <x-filament::section>
        <div class="kk-rise flex flex-col gap-10 py-2">
            {{-- Masthead: eyebrow + display heading + één intro-regel. --}}
            <div class="max-w-2xl">
                <p class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#586573]">
                    Aan de slag
                </p>

                <h2 class="mt-4 text-[clamp(1.75rem,3vw,2.75rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#0f1720]">
                    Zet je eerste kot online
                </h2>

                <p class="mt-4 max-w-xl text-sm leading-[1.45] tracking-[-0.01em] text-[#586573]">
                    Je eerste kot is in drie stappen live — gemiddeld tien minuten werk. Voeg een gebouw toe, koppel er kamers aan en publiceer.
                </p>
            </div>

            {{-- Genummerde 3-staps gids. Stap 1 is af zodra er een gebouw is. --}}
            @php
                $steps = [
                    [
                        'n' => '01',
                        'title' => 'Voeg een gebouw toe',
                        'body' => 'Begin met het pand: adres, naam en de basisgegevens van je gebouw.',
                        'done' => $hasBuildings,
                        'active' => ! $hasBuildings,
                    ],
                    [
                        'n' => '02',
                        'title' => 'Voeg kamers toe',
                        'body' => 'Maak per gebouw je kamers aan met prijs, oppervlakte en foto’s.',
                        'done' => false,
                        'active' => $hasBuildings,
                    ],
                    [
                        'n' => '03',
                        'title' => 'Publiceer & ontvang aanvragen',
                        'body' => 'Zet je kamers live en ontvang aanvragen van studenten op zoek naar een kot.',
                        'done' => false,
                        'active' => false,
                    ],
                ];
            @endphp

            <ol class="flex flex-col">
                @foreach ($steps as $step)
                    <li @class([
                        'flex flex-col gap-5 border-t border-[#0f17201f] py-7 sm:flex-row sm:items-start sm:gap-8',
                        'sm:border-b' => $loop->last,
                    ])>
                        {{-- Stap-marker: cerulean check als af, anders micro-caps cijfer. --}}
                        <div class="shrink-0">
                            @if ($step['done'])
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-[3px] border border-[#3a6ea5] text-[#3a6ea5]" aria-label="Voltooid">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M4 12.5 9.5 18 20 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </span>
                            @else
                                <span @class([
                                    'inline-flex h-9 w-9 items-center justify-center rounded-[3px] text-[0.625rem] font-semibold uppercase tracking-[0.12em] tabular-nums',
                                    'border border-[#0f1720] text-[#0f1720]' => $step['active'],
                                    'border border-[#0f17201f] text-[#9aa6b4]' => ! $step['active'],
                                ])>
                                    {{ $step['n'] }}
                                </span>
                            @endif
                        </div>

                        <div class="flex-1">
                            <h3 @class([
                                'text-lg font-medium tracking-[-0.01em]',
                                'text-[#0f1720]' => $step['active'] || $step['done'],
                                'text-[#9aa6b4]' => ! $step['active'] && ! $step['done'],
                            ])>
                                {{ $step['title'] }}
                            </h3>

                            <p @class([
                                'mt-1.5 max-w-xl text-sm leading-[1.45] tracking-[-0.01em]',
                                'text-[#586573]' => $step['active'] || $step['done'],
                                'text-[#9aa6b4]' => ! $step['active'] && ! $step['done'],
                            ])>
                                {{ $step['body'] }}
                            </p>
                        </div>

                        {{-- Eén heldere volgende actie: de CTA hangt aan de actieve stap.
                             Stap 1 (geen gebouw) → gebouw toevoegen; stap 2 (gebouw, geen
                             kamers) → kamers toevoegen op de gebouwpagina. --}}
                        @if ($step['active'] && ! $hasBuildings)
                            <div class="shrink-0 sm:pt-0.5">
                                <a href="{{ $createBuildingUrl }}"
                                   class="group inline-flex h-11 items-center gap-3 rounded-[4px] bg-[#002f5b] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                                    Voeg gebouw toe
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                                        <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </span>
                                </a>
                            </div>
                        @elseif ($step['active'] && $hasBuildings && $addRoomsUrl)
                            <div class="shrink-0 sm:pt-0.5">
                                <a href="{{ $addRoomsUrl }}"
                                   class="group inline-flex h-11 items-center gap-3 rounded-[4px] bg-[#002f5b] pl-5 pr-1.5 text-xs font-medium uppercase tracking-[0.04em] text-white transition-colors duration-300 hover:bg-[#001f3d]">
                                    Voeg kamers toe
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[3px] bg-[#ff6700]">
                                        <svg class="h-4 w-4 transition-transform duration-500 ease-[cubic-bezier(0.65,0,0.35,1)] group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </span>
                                </a>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
