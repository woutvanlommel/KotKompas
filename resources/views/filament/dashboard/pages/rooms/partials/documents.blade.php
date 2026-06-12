@php
    $tenantDocs  = $this->getTenantDocuments();
    $roomContracts = $this->getRoomContracts();
    $tenant      = $room->activeTenant() ?? $room->tenant;
@endphp

<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-semibold text-gray-900">Documenten</h2>

        @if ($tenant)
            <button
                wire:click="mountAction('createContract')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-primary-600 hover:bg-primary-700 transition"
            >
                <x-heroicon-o-document-plus class="w-3.5 h-3.5" />
                Contract aanmaken
            </button>
        @endif
    </div>

    {{-- Contracten --}}
    @if ($roomContracts->isNotEmpty())
        <div class="mb-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Contracten</p>
            <div class="space-y-2">
                @foreach ($roomContracts as $contract)
                    @php
                        $pdfUrl         = route('contracts.pdf', $contract);
                        $handtekeningen = $contract->blocks['ondertekening']['handtekeningen'] ?? [];
                        $userHasSigned  = collect($handtekeningen)->contains('user_id', auth()->id());
                        $signedCount    = count($handtekeningen);
                        $totalCount     = ($contract->rentalPeriod?->tenants?->count() ?? 0) + 1; // +1 voor verhuurder

                        $statusColor = match(true) {
                            $contract->status === 'signed'   => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'label' => 'Volledig ondertekend'],
                            $contract->status === 'archived' => ['bg' => 'bg-gray-100',  'text' => 'text-gray-500',  'label' => 'Gearchiveerd'],
                            $signedCount > 0                 => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',  'label' => "{$signedCount}/{$totalCount} ondertekend"],
                            default                          => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700', 'label' => 'Wacht op ondertekening'],
                        };
                    @endphp

                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-gray-100 bg-gray-50">
                        <div class="flex-shrink-0 w-8 h-11 bg-green-50 rounded-lg flex items-center justify-center border border-green-100">
                            <x-heroicon-o-document-check class="w-4 h-4 text-green-600" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $contract->name ?? 'Huurcontract' }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $statusColor['bg'] }} {{ $statusColor['text'] }}">
                                    {{ $statusColor['label'] }}
                                </span>
                                @if ($contract->rentalPeriod?->tenants?->isNotEmpty())
                                    <span class="text-xs text-gray-400">
                                        {{ $contract->rentalPeriod->tenants->pluck('full_name')->join(', ') }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $contract->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if ($contract->status === 'draft' && ! $userHasSigned)
                                <button
                                    wire:click="mountAction('signContract', { documentId: {{ $contract->id }} })"
                                    class="text-xs py-1 px-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition-colors"
                                >
                                    <x-heroicon-o-pencil class="w-3 h-3 inline mr-0.5" />
                                    Ondertekenen
                                </button>
                            @elseif ($userHasSigned && $contract->status !== 'signed')
                                <span class="text-xs text-green-700">
                                    <x-heroicon-o-check class="w-3.5 h-3.5 inline" />
                                    Getekend
                                </span>
                            @endif

                            <a href="{{ $pdfUrl }}" target="_blank"
                                class="p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 transition-colors"
                                title="PDF bekijken">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Publieke docs van huurder --}}
    @if ($tenant)
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                Documenten van {{ $tenant->first_name ?? $tenant->name }}
            </p>

            @if ($tenantDocs->isEmpty())
                <div class="flex items-center gap-3 py-4 text-center justify-center text-gray-400">
                    <x-heroicon-o-document-text class="w-5 h-5" />
                    <p class="text-sm">Geen publieke documenten gedeeld</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($tenantDocs as $doc)
                        @php
                            $media = $doc->getFirstMedia('document');
                            $thumb = ($media?->hasGeneratedConversion('thumbnail'))
                                        ? $doc->getFirstMediaUrl('document', 'thumbnail')
                                        : null;
                            $url   = $doc->getFirstMediaUrl('document');
                            $isPdf = $media?->mime_type === 'application/pdf';
                        @endphp

                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-gray-100 bg-gray-50">
                            <a href="{{ $url }}" target="_blank"
                                class="flex-shrink-0 w-8 h-11 bg-gray-200 rounded-lg overflow-hidden border border-gray-200">
                                @if ($thumb)
                                    <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover" />
                                @elseif ($isPdf)
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <x-heroicon-o-document-text class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <x-heroicon-o-photo class="w-4 h-4" />
                                    </div>
                                @endif
                            </a>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->name }}</p>
                                <p class="text-xs text-gray-400">{{ $doc->created_at->format('d/m/Y') }}</p>
                            </div>

                            <a href="{{ $url }}" target="_blank"
                                class="flex-shrink-0 p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 transition-colors">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="flex items-center gap-3 py-4 justify-center text-gray-400">
            <x-heroicon-o-no-symbol class="w-5 h-5" />
            <p class="text-sm">Geen actieve huurder gekoppeld</p>
        </div>
    @endif
</div>
