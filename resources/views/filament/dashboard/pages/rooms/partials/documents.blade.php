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
                        $totalCount     = ($contract->rentalPeriod?->tenants?->count() ?? 0) + 1;

                        $period    = $contract->rentalPeriod;
                        $startDate = $period?->start_date?->format('d/m/Y') ?? ($contract->blocks['huurperiode']['start'] ? \Carbon\Carbon::parse($contract->blocks['huurperiode']['start'])->format('d/m/Y') : null);
                        $endDate   = $period?->end_date?->format('d/m/Y') ?? ($contract->blocks['huurperiode']['einde'] ? \Carbon\Carbon::parse($contract->blocks['huurperiode']['einde'])->format('d/m/Y') : null);

                        $statusColor = match(true) {
                            $contract->status === 'signed'   => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'dot' => 'bg-green-500', 'label' => 'Volledig ondertekend'],
                            $contract->status === 'archived' => ['bg' => 'bg-gray-100',  'text' => 'text-gray-500',  'dot' => 'bg-gray-400',  'label' => 'Gearchiveerd'],
                            $signedCount > 0                 => ['bg' => 'bg-blue-100',  'text' => 'text-blue-700',  'dot' => 'bg-blue-500',  'label' => "{$signedCount}/{$totalCount} ondertekend"],
                            default                          => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'dot' => 'bg-amber-400', 'label' => 'Wacht op ondertekening'],
                        };
                    @endphp

                    <div class="flex items-start gap-3 px-3 py-3 rounded-xl border border-gray-100 bg-gray-50 hover:bg-white hover:border-gray-200 transition-colors">
                        <div class="flex-shrink-0 w-8 h-10 bg-green-50 rounded-lg flex items-center justify-center border border-green-100 mt-0.5">
                            <x-heroicon-o-document-check class="w-4 h-4 text-green-600" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $contract->name ?? 'Huurcontract' }}
                            </p>

                            {{-- Periode --}}
                            @if ($startDate)
                                <p class="text-xs text-gray-500 mt-0.5 font-medium">
                                    <x-heroicon-o-calendar-days class="w-3 h-3 inline mr-0.5 text-gray-400" />
                                    {{ $startDate }}{{ $endDate ? ' → ' . $endDate : ' → heden' }}
                                </p>
                            @endif

                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-1.5 py-0.5 rounded-full {{ $statusColor['bg'] }} {{ $statusColor['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusColor['dot'] }} inline-block"></span>
                                    {{ $statusColor['label'] }}
                                </span>

                                @if ($period?->tenants?->isNotEmpty())
                                    <span class="text-xs text-gray-400 truncate">
                                        {{ $period->tenants->pluck('full_name')->join(', ') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1 flex-shrink-0 mt-0.5">
                            @if ($contract->status === 'draft' && ! $userHasSigned)
                                <button
                                    wire:click="mountAction('signContract', { documentId: {{ $contract->id }} })"
                                    class="text-xs py-1 px-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition-colors"
                                >
                                    <x-heroicon-o-pencil class="w-3 h-3 inline mr-0.5" />
                                    Ondertekenen
                                </button>
                            @elseif ($userHasSigned && $contract->status !== 'signed')
                                <span class="text-xs text-green-700 font-medium">
                                    <x-heroicon-o-check class="w-3.5 h-3.5 inline" /> Getekend
                                </span>
                            @endif

                            <a href="{{ $pdfUrl }}" target="_blank"
                                class="p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 transition-colors"
                                title="PDF bekijken">
                                <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                            </a>

                            <button
                                wire:click="mountAction('deleteContract', { documentId: {{ $contract->id }} })"
                                class="p-1.5 rounded-lg border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-colors"
                                title="Verwijderen">
                                <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            </button>
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
