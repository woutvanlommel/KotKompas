@props(['room'])

<div class="grid gap-10 lg:grid-cols-[1fr_320px] lg:items-start">

    {{-- Beschrijving --}}
    <div>
        <p class="mb-4 inline-flex items-center gap-3 text-[0.625rem] font-medium uppercase tracking-[0.18em] text-ink/55">
            <span class="inline-block h-px w-9 bg-accent-500"></span> Over dit kot
        </p>
        @if ($room->description ?? null)
            <div class="kk-richtext">
                @richtext($room->description)
            </div>
        @else
            <p class="text-sm italic text-ink/40">Geen beschrijving toegevoegd.</p>
        @endif
    </div>

    {{-- Verhuurder kaartje --}}
    @php
        $landlord    = $room->building?->landlord;
        $viewer      = auth()->user();
        $isOwner     = $landlord && $viewer && $landlord->id === $viewer->id;
        $canMessage  = $landlord && $viewer && ($viewer->hasRole('huurder') ?? false) && ! $isOwner;
        $memberSince = $landlord?->created_at?->format('Y');
        $landlordInitial = strtoupper(mb_substr($landlord?->name ?? 'V', 0, 1));
    @endphp

    @if ($canMessage)
        {{-- Huurder: in-app berichtkanaal naar de verhuurder --}}
        <div class="rounded-2xl border border-hairline bg-canvas-deep p-6 lg:sticky lg:top-28"
             x-data="{ open: {{ $errors->has('body') ? 'true' : 'false' }} }">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-900 text-lg font-medium text-white">{{ $landlordInitial }}</div>
                <div>
                    <p class="text-sm font-medium text-ink">{{ $landlord->full_name }}</p>
                    @if ($memberSince)
                        <p class="text-xs text-ink/55">Lid sinds {{ $memberSince }}</p>
                    @endif
                </div>
            </div>

            @if (session('status'))
                <div class="mt-4 flex items-start gap-2 rounded-xl border border-accent-500/30 bg-accent-500/10 px-4 py-3 text-xs leading-relaxed text-ink/80">
                    <x-heroicon-o-check-circle class="mt-px h-4 w-4 shrink-0 text-accent-600" aria-hidden="true" />
                    <span>{{ session('status') }}</span>
                </div>
            @else
                <p class="mt-4 border-t border-hairline pt-4 text-sm text-ink/65">
                    Interesse in dit kot? Stuur de verhuurder rechtstreeks een bericht via KotKompas.
                </p>

                <button type="button" x-show="!open" @click="open = true"
                        class="mt-4 w-full rounded-xl bg-primary-900 py-3 text-sm font-medium text-white transition-colors hover:bg-ink">
                    Stuur een bericht
                </button>

                <form method="POST" action="{{ route('rooms.contact', $room) }}" x-show="open" x-cloak class="mt-4 space-y-3">
                    @csrf
                    <textarea name="body" rows="4" required maxlength="5000"
                              placeholder="Stel je vraag of toon je interesse…"
                              class="w-full rounded-xl border border-hairline bg-canvas px-3 py-2.5 text-sm text-ink placeholder:text-ink/40 focus:border-ink/30 focus:outline-none focus:ring-2 focus:ring-primary-500/30">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 rounded-xl bg-primary-900 py-2.5 text-sm font-medium text-white transition-colors hover:bg-ink">
                            Versturen
                        </button>
                        <button type="button" @click="open = false"
                                class="rounded-xl border border-hairline px-4 py-2.5 text-sm text-ink/70 transition-colors hover:bg-ink/[0.04]">
                            Annuleer
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @else
        {{-- Gast / verhuurder: contactkanaal achter login --}}
        <div class="relative rounded-2xl border border-hairline bg-canvas-deep p-6 lg:sticky lg:top-28">

            {{-- Geblurde placeholder --}}
            <div class="select-none space-y-4 blur-sm" aria-hidden="true">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-900 text-lg font-medium text-white">{{ $landlordInitial }}</div>
                    <div>
                        <p class="text-sm font-medium text-ink">{{ $landlord?->full_name ?? 'Verhuurder' }}</p>
                        <p class="text-xs text-ink/55">Lid sinds {{ $memberSince ?? '2024' }}</p>
                    </div>
                </div>
                <button disabled class="w-full rounded-xl bg-primary-900 py-3 text-sm font-medium text-white opacity-60">
                    Contacteer verhuurder
                </button>
            </div>

            {{-- Lock overlay --}}
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 rounded-2xl bg-canvas/70 px-5 text-center backdrop-blur-[2px]">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-900/10">
                    <x-heroicon-o-lock-closed class="h-5 w-5 text-primary-900" aria-hidden="true" />
                </div>
                @if ($isOwner)
                    <p class="text-sm font-medium text-ink">Dit is jouw kot</p>
                    <p class="max-w-[14rem] text-xs leading-relaxed text-ink/55">Geïnteresseerde huurders kunnen je hier rechtstreeks een bericht sturen.</p>
                @elseif ($viewer)
                    <p class="text-sm font-medium text-ink">Verhuurdergegevens</p>
                    <p class="max-w-[14rem] text-xs leading-relaxed text-ink/55">Berichten sturen kan met een huurdersaccount.</p>
                @else
                    <p class="text-sm font-medium text-ink">Contacteer de verhuurder</p>
                    <p class="max-w-[14rem] text-xs leading-relaxed text-ink/55">Log in als huurder om dit kot rechtstreeks aan te vragen.</p>
                    <a href="{{ url('/dashboard/login') }}"
                       class="mt-1 inline-flex items-center rounded-[4px] bg-primary-900 px-4 py-2 text-xs font-medium text-white transition-colors hover:bg-ink">
                        Log in om te contacteren
                    </a>
                @endif
            </div>

        </div>
    @endif

</div>
