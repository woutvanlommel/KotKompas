<x-filament-panels::page>

    @php
        $documents = $this->getDocuments(); 
        $contracts = $this->getContracts();
    @endphp

    {{-- ===== CONTRACTEN ===== --}}
    @if ($contracts->isNotEmpty())
        <section class="mb-8">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <x-heroicon-o-document-check class="w-5 h-5 text-green-500" />
                Contracten
                <span class="text-sm font-normal text-gray-400">({{ $contracts->count() }})</span>
            </h2>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($contracts as $contract)
                    @php
                        $pdfUrl         = route('contracts.pdf', $contract);
                        $handtekeningen = $contract->blocks['ondertekening']['handtekeningen'] ?? [];
                        $userHasSigned  = collect($handtekeningen)->contains('user_id', auth()->id());
                        $signedCount    = count($handtekeningen);
                        $totalCount     = ($contract->rentalPeriod?->tenants?->count() ?? 0) + 1;

                        $period     = $contract->rentalPeriod;
                        $startDate  = $period?->start_date?->format('d/m/Y') ?? ($contract->blocks['huurperiode']['start'] ? \Carbon\Carbon::parse($contract->blocks['huurperiode']['start'])->format('d/m/Y') : null);
                        $endDate    = $period?->end_date?->format('d/m/Y') ?? ($contract->blocks['huurperiode']['einde'] ? \Carbon\Carbon::parse($contract->blocks['huurperiode']['einde'])->format('d/m/Y') : null);

                        $statusColor = match(true) {
                            $contract->status === 'signed'   => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'dot' => 'bg-green-500', 'label' => 'Volledig ondertekend'],
                            $contract->status === 'archived' => ['bg' => 'bg-gray-100 dark:bg-gray-700',      'text' => 'text-gray-500 dark:text-gray-400',  'dot' => 'bg-gray-400', 'label' => 'Gearchiveerd'],
                            $signedCount > 0                 => ['bg' => 'bg-blue-100 dark:bg-blue-900/30',   'text' => 'text-blue-700 dark:text-blue-300',   'dot' => 'bg-blue-500', 'label' => "{$signedCount}/{$totalCount} ondertekend"],
                            default                          => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-300', 'dot' => 'bg-amber-400', 'label' => 'Wacht op ondertekening'],
                        };
                    @endphp

                    <div class="flex items-center gap-4 px-4 py-3.5 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        {{-- Icoon --}}
                        <div class="flex-shrink-0 w-9 h-12 bg-green-50 dark:bg-green-900/20 rounded-lg flex items-center justify-center border border-green-100 dark:border-green-800">
                            <x-heroicon-o-document-check class="w-4.5 h-4.5 text-green-600 dark:text-green-400" />
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                {{ $contract->name ?? 'Huurcontract' }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                {{-- Status badge --}}
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $statusColor['bg'] }} {{ $statusColor['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusColor['dot'] }} inline-block"></span>
                                    {{ $statusColor['label'] }}
                                </span>

                                {{-- Periode --}}
                                @if ($startDate)
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                        <x-heroicon-o-calendar-days class="w-3 h-3 inline mr-0.5 text-gray-400" />
                                        {{ $startDate }}{{ $endDate ? ' → ' . $endDate : ' → heden' }}
                                    </span>
                                @endif

                                {{-- Kamer --}}
                                @if ($period)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        Kamer {{ $period->room->room_number }} · {{ $period->room->building->street }} {{ $period->room->building->house_number }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Acties --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if ($contract->status === 'draft' && ! $userHasSigned)
                                <button
                                    wire:click="mountAction('signContract', { documentId: {{ $contract->id }} })"
                                    class="text-xs py-1.5 px-3 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition-colors"
                                >
                                    <x-heroicon-o-pencil class="w-3.5 h-3.5 inline mr-1" />
                                    Ondertekenen
                                </button>
                            @elseif ($userHasSigned && $contract->status !== 'signed')
                                <span class="text-xs py-1 px-2.5 rounded-full bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 font-medium">
                                    <x-heroicon-o-check class="w-3 h-3 inline mr-0.5" />
                                    Jij hebt getekend
                                </span>
                            @endif

                            <a href="{{ $pdfUrl }}" target="_blank"
                                class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                                title="PDF bekijken">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>

                            <button
                                wire:click="mountAction('deleteContract', { documentId: {{ $contract->id }} })"
                                class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                title="Verwijderen">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== MIJN DOCUMENTEN ===== --}}
    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-folder-open class="w-5 h-5 text-gray-400" />
                Mijn documenten
                <span class="text-sm font-normal text-gray-400">({{ $documents->total() }})</span>
            </h2>

            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button
                    wire:click="toggleViewMode('card')"
                    class="p-2 transition-colors
                        {{ $viewMode === 'card'
                            ? 'bg-primary-600 text-white'
                            : 'bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}"
                    title="Kaartweergave"
                >
                    <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                </button>
                <button
                    wire:click="toggleViewMode('list')"
                    class="p-2 transition-colors border-l border-gray-200 dark:border-gray-700
                        {{ $viewMode === 'list'
                            ? 'bg-primary-600 text-white'
                            : 'bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}"
                    title="Lijstweergave"
                >
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Lege staat --}}
        @if ($documents->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <x-heroicon-o-document-text class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" />
                <p class="text-gray-500 dark:text-gray-400 font-medium">Nog geen documenten</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload je eerste document via de knop rechts bovenaan.</p>
            </div>

        {{-- Kaartweergave --}}
        @elseif ($viewMode === 'card')
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($documents as $document)
                    @php
                        $media     = $document->getFirstMedia('document');
                        $thumb     = ($media?->hasGeneratedConversion('thumbnail'))
                                        ? $document->getFirstMediaUrl('document', 'thumbnail')
                                        : null;
                        $url       = $document->getFirstMediaUrl('document');
                        $isPdf     = $media?->mime_type === 'application/pdf';
                        $typeLabel = $this::getTypeLabel($document->type);
                        $typeColor = $this::getTypeColor($document->type);
                    @endphp

                    <div class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <a href="{{ $url }}" target="_blank" class="block bg-gray-100 dark:bg-gray-900 overflow-hidden" style="aspect-ratio: 3/4">
                            @if ($thumb)
                                <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            @elseif ($isPdf)
                                <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-gray-400 dark:text-gray-500">
                                    <x-heroicon-o-document-text class="w-10 h-10" />
                                    <span class="text-xs">PDF</span>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                    <x-heroicon-o-photo class="w-10 h-10" />
                                </div>
                            @endif
                        </a>

                        <span class="absolute top-2 left-2 text-xs font-medium px-2 py-0.5 rounded-full
                            @if ($typeColor === 'blue')    bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                            @elseif ($typeColor === 'amber') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                            @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                            {{ $typeLabel }}
                        </span>

                        @if ($document->is_public)
                            <span class="absolute top-2 right-2 text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                Publiek
                            </span>
                        @endif

                        <div class="p-3 flex flex-col gap-2">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-200 truncate" title="{{ $document->name }}">
                                {{ $document->name }}
                            </p>
                            @if ($document->rentalPeriod)
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                    Kamer {{ $document->rentalPeriod->room->room_number }}
                                </p>
                            @endif
                            <div class="flex items-center gap-1 mt-1">
                                <button
                                    wire:click="togglePublic({{ $document->id }})"
                                    class="flex-1 text-xs py-1 px-2 rounded-lg border transition-colors
                                        {{ $document->is_public
                                            ? 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:border-emerald-800 dark:text-emerald-400 dark:bg-emerald-900/20'
                                            : 'border-gray-200 text-gray-500 bg-gray-50 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:bg-gray-800' }}"
                                >
                                    @if ($document->is_public)
                                        <x-heroicon-o-eye class="w-3 h-3 inline" /> Publiek
                                    @else
                                        <x-heroicon-o-eye-slash class="w-3 h-3 inline" /> Privé
                                    @endif
                                </button>
                                <button
                                    wire:click="deleteDocument({{ $document->id }})"
                                    wire:confirm="Ben je zeker dat je dit document wil verwijderen?"
                                    class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 dark:hover:border-red-800 dark:hover:bg-red-900/20 transition-colors"
                                >
                                    <x-heroicon-o-trash class="w-3 h-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($documents->hasPages())
                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
            @endif

        {{-- Lijstweergave --}}
        @else
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($documents as $document)
                    @php
                        $media     = $document->getFirstMedia('document');
                        $thumb     = ($media?->hasGeneratedConversion('thumbnail'))
                                        ? $document->getFirstMediaUrl('document', 'thumbnail')
                                        : null;
                        $url       = $document->getFirstMediaUrl('document');
                        $isPdf     = $media?->mime_type === 'application/pdf';
                        $typeLabel = $this::getTypeLabel($document->type);
                        $typeColor = $this::getTypeColor($document->type);
                    @endphp

                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <a href="{{ $url }}" target="_blank" class="flex-shrink-0 w-10 h-14 bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            @if ($thumb)
                                <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover" />
                            @elseif ($isPdf)
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <x-heroicon-o-document-text class="w-5 h-5" />
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <x-heroicon-o-photo class="w-5 h-5" />
                                </div>
                            @endif
                        </a>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $document->name }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs px-1.5 py-0.5 rounded-full
                                    @if ($typeColor === 'blue')    bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                    @elseif ($typeColor === 'amber') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                    @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ $typeLabel }}
                                </span>
                                @if ($document->rentalPeriod)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                        Kamer {{ $document->rentalPeriod->room->room_number }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $document->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button
                                wire:click="togglePublic({{ $document->id }})"
                                class="text-xs py-1 px-2.5 rounded-lg border transition-colors
                                    {{ $document->is_public
                                        ? 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:border-emerald-800 dark:text-emerald-400 dark:bg-emerald-900/20'
                                        : 'border-gray-200 text-gray-500 bg-gray-50 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:bg-gray-800' }}"
                            >
                                @if ($document->is_public)
                                    <x-heroicon-o-eye class="w-3 h-3 inline" /> Publiek
                                @else
                                    <x-heroicon-o-eye-slash class="w-3 h-3 inline" /> Privé
                                @endif
                            </button>
                            <a href="{{ $url }}" target="_blank"
                                class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-primary-600 hover:border-primary-200 hover:bg-primary-50 dark:hover:border-primary-800 dark:hover:bg-primary-900/20 transition-colors">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>
                            <button
                                wire:click="deleteDocument({{ $document->id }})"
                                wire:confirm="Ben je zeker dat je dit document wil verwijderen?"
                                class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 dark:hover:border-red-800 dark:hover:bg-red-900/20 transition-colors">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($documents->hasPages())
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            @endif
        @endif
    </section>

</x-filament-panels::page>
