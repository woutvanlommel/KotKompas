@php
    $activePeriod  = $room->rentalPeriods()
        ->with('tenants')
        ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
        ->latest('start_date')
        ->first();

    $primaryTenant = $activePeriod?->primaryTenant();
    $coTenants     = $activePeriod
        ? $activePeriod->tenants->where('pivot.is_primary', false)->values()
        : collect();
@endphp

<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-semibold text-gray-900">Status & Huurder</h2>
        <button wire:click="mountAction('updateStatus')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Status wijzigen
        </button>
    </div>

    {{-- Status badge --}}
    <div class="flex items-center gap-2 mb-6">
        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-[0.375rem] text-xs font-semibold tabular-nums
            {{ $status['bg'] }} {{ $status['text'] }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
            {{ $status['label'] }}
        </span>
        @if ($activePeriod)
            <span class="text-xs text-gray-400">
                vanaf {{ $activePeriod->start_date->format('d/m/Y') }}
            </span>
        @endif
    </div>

    {{-- Huurders --}}
    <div class="border-t border-gray-100 pt-5">

        {{-- Hoofdhuurder --}}
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-700">Hoofdhuurder</p>
            <div class="flex items-center gap-2">
                @if ($primaryTenant)
                    <button wire:click="mountAction('linkTenant')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                        </svg>
                        Wijzigen
                    </button>
                    <button wire:click="mountAction('unlinkTenant')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Beëindigen
                    </button>
                @else
                    <button wire:click="mountAction('linkTenant')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                        </svg>
                        Huurder koppelen
                    </button>
                @endif
            </div>
        </div>

        @if ($primaryTenant)
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <span class="text-sm font-semibold text-blue-700">
                        {{ strtoupper(substr($primaryTenant->name, 0, 1)) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $primaryTenant->full_name }}</p>
                    <a href="mailto:{{ $primaryTenant->email }}" class="text-xs text-gray-500 hover:text-gray-700 truncate block">
                        {{ $primaryTenant->email }}
                    </a>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-400 italic mb-4">Geen huurder gekoppeld.</p>
        @endif

        {{-- Medehuurders --}}
        @if ($activePeriod)
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-700">Medehuurders</p>
                <button wire:click="mountAction('addCoTenant')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                    </svg>
                    Toevoegen
                </button>
            </div>

            @if ($coTenants->isEmpty())
                <p class="text-xs text-gray-400 italic">Geen medehuurders.</p>
            @else
                <div class="space-y-2">
                    @foreach ($coTenants as $coTenant)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                <span class="text-xs font-semibold text-gray-600">
                                    {{ strtoupper(substr($coTenant->name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $coTenant->full_name }}</p>
                                <a href="mailto:{{ $coTenant->email }}" class="text-xs text-gray-400 hover:text-gray-600 truncate block">
                                    {{ $coTenant->email }}
                                </a>
                            </div>
                            <button
                                wire:click="mountAction('removeCoTenant', { tenantId: {{ $coTenant->id }} })"
                                wire:confirm="Ben je zeker dat je {{ $coTenant->full_name }} als medehuurder wil verwijderen?"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                title="Verwijderen"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Review invitations --}}
    @php $pendingInvitations = $room->pendingReviewInvitations()->with('tenant')->get(); @endphp
    @if ($pendingInvitations->isNotEmpty())
        <div class="border-t border-gray-100 pt-5 mt-5">
            <p class="text-sm font-medium text-gray-700 mb-3">{{ Str::plural('Beoordelingslink', $pendingInvitations->count()) }}</p>
            <div class="space-y-4">
                @foreach ($pendingInvitations as $invitation)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-xs font-medium text-gray-600">{{ $invitation->tenant?->name ?? 'Ex-huurder' }}</p>
                            @if ($invitation->isOpen())
                                <span class="text-xs text-gray-400">geldig tot {{ $invitation->expires_at->format('d-m-Y') }}</span>
                            @else
                                <span class="text-xs font-medium text-red-500">verlopen</span>
                            @endif
                        </div>
                        @if ($invitation->isOpen())
                            <div x-data="{ copied: false }" class="flex items-center gap-2">
                                <input type="text" readonly value="{{ $invitation->url() }}"
                                       x-on:focus="$el.select()"
                                       class="w-full truncate rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-600 focus:outline-none">
                                <button type="button"
                                        x-on:click="navigator.clipboard.writeText('{{ $invitation->url() }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="inline-flex shrink-0 items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                    </svg>
                                    <span x-show="!copied">Kopieer</span>
                                    <span x-show="copied" x-cloak>Gekopieerd!</span>
                                </button>
                            </div>
                        @else
                            <button wire:click="mountAction('reissueReviewInvitation', { invitation: {{ $invitation->id }} })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:bg-gray-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Nieuwe link maken
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">De ex-huurder beoordeelt het kot anoniem via zijn link.</p>
        </div>
    @endif
</div>
