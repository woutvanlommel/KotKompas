<x-filament-panels::page>

    @php
        $documents = $this->getDocuments();
        $contracts = $this->getContracts();
        $ocrPending = $documents->getCollection()->contains(fn ($d) => in_array($d->ocr_status, [\App\Models\Document::OCR_PENDING, \App\Models\Document::OCR_PROCESSING], true));
    @endphp

    {{-- ===== CONTRACTEN ===== --}}
    @if ($contracts->isNotEmpty())
        <section class="mb-8">
            <h2 class="text-base font-semibold text-[#0f1720] mb-3 flex items-center gap-2">
                <x-heroicon-o-document-check class="w-5 h-5 text-green-500" />
                Contracten
                <span class="text-sm font-normal text-[#9aa6b4]">({{ $contracts->count() }})</span>
            </h2>

            <div class="bg-white rounded-xl border border-[#0f17201f] overflow-hidden divide-y divide-[#0f17201f]">
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
                            $contract->status === 'signed'   => ['bg' => 'bg-green-100',     'text' => 'text-green-700',    'dot' => 'bg-green-500',    'label' => 'Volledig ondertekend'],
                            $contract->status === 'archived' => ['bg' => 'bg-[#edf0f4]',     'text' => 'text-[#586573]',   'dot' => 'bg-[#9aa6b4]',   'label' => 'Gearchiveerd'],
                            $signedCount > 0                 => ['bg' => 'bg-[#e1e6ed]',     'text' => 'text-[#0f1720]',   'dot' => 'bg-[#0f1720]',   'label' => "{$signedCount}/{$totalCount} ondertekend"],
                            default                          => ['bg' => 'bg-amber-100',     'text' => 'text-amber-700',   'dot' => 'bg-amber-400',   'label' => 'Wacht op ondertekening'],
                        };
                    @endphp

                    <div class="flex items-center gap-4 px-4 py-3.5 hover:bg-[#edf0f4] transition-colors">
                        {{-- Icoon --}}
                        <div class="flex-shrink-0 w-9 h-12 bg-green-50 rounded-[4px] flex items-center justify-center border border-green-100">
                            <x-heroicon-o-document-check class="w-4.5 h-4.5 text-green-600" />
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#0f1720] truncate">
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
                                    <span class="text-xs text-[#586573] font-medium">
                                        <x-heroicon-o-calendar-days class="w-3 h-3 inline mr-0.5 text-[#9aa6b4]" />
                                        {{ $startDate }}{{ $endDate ? ' → ' . $endDate : ' → heden' }}
                                    </span>
                                @endif

                                {{-- Kamer --}}
                                @if ($period)
                                    <span class="text-xs text-[#9aa6b4]">
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
                                    class="text-xs py-1.5 px-3 rounded-[4px] bg-green-600 hover:bg-green-700 text-white font-medium transition-colors"
                                >
                                    <x-heroicon-o-pencil class="w-3.5 h-3.5 inline mr-1" />
                                    Ondertekenen
                                </button>
                            @elseif ($userHasSigned && $contract->status !== 'signed')
                                <span class="text-xs py-1 px-2.5 rounded-full bg-green-50 text-green-700 font-medium">
                                    <x-heroicon-o-check class="w-3 h-3 inline mr-0.5" />
                                    Jij hebt getekend
                                </span>
                            @endif

                            <a href="{{ $pdfUrl }}" target="_blank"
                                class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-[#0f1720] hover:border-[#0f17201f] hover:bg-[#edf0f4] transition-colors"
                                title="PDF bekijken">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>

                            @if (auth()->user()->hasRole('verhuurder'))
                                <button
                                    wire:click="mountAction('deleteContract', { documentId: {{ $contract->id }} })"
                                    class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-colors"
                                    title="Verwijderen">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== MIJN DOCUMENTEN ===== --}}
    <section @if ($ocrPending) wire:poll.10s @endif>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-[#0f1720] flex items-center gap-2">
                <x-heroicon-o-folder-open class="w-5 h-5 text-[#9aa6b4]" />
                Mijn documenten
                <span class="text-sm font-normal text-[#9aa6b4]">({{ $documents->total() }})</span>
            </h2>

            <div class="inline-flex rounded-[4px] border border-[#0f17201f] overflow-hidden">
                <button
                    wire:click="toggleViewMode('card')"
                    class="p-2 transition-colors
                        {{ $viewMode === 'card'
                            ? 'bg-[#0f1720] text-white'
                            : 'bg-white text-[#586573] hover:bg-[#edf0f4]' }}"
                    title="Kaartweergave"
                >
                    <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                </button>
                <button
                    wire:click="toggleViewMode('list')"
                    class="p-2 transition-colors border-l border-[#0f17201f]
                        {{ $viewMode === 'list'
                            ? 'bg-[#0f1720] text-white'
                            : 'bg-white text-[#586573] hover:bg-[#edf0f4]' }}"
                    title="Lijstweergave"
                >
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Lege staat --}}
        @if ($documents->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <x-heroicon-o-document-text class="w-12 h-12 text-[#9aa6b4] mb-3" />
                <p class="text-[#586573] font-medium">Nog geen documenten</p>
                <p class="text-sm text-[#9aa6b4] mt-1">Upload je eerste document via de knop rechts bovenaan.</p>
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
                        $url       = route('documents.download', $document);
                        $isPdf     = $media?->mime_type === 'application/pdf';
                        $typeLabel = $this::getTypeLabel($document->type);
                        $typeColor = $this::getTypeColor($document->type);
                        $ocrStatus = $document->ocr_status;
                    @endphp

                    <div class="group relative flex flex-col bg-white rounded-xl border border-[#0f17201f] overflow-hidden hover:shadow-md transition-shadow">
                        <a href="{{ $url }}" target="_blank" class="block bg-[#edf0f4] overflow-hidden" style="aspect-ratio: 3/4">
                            @if ($thumb)
                                <img src="{{ route('documents.download', ['document' => $document, 'conversion' => 'thumbnail']) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            @elseif ($isPdf)
                                <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-[#9aa6b4]">
                                    <x-heroicon-o-document-text class="w-10 h-10" />
                                    <span class="text-xs">PDF</span>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#9aa6b4]">
                                    <x-heroicon-o-photo class="w-10 h-10" />
                                </div>
                            @endif
                        </a>

                        <span class="absolute top-2 left-2 text-xs font-medium px-2 py-0.5 rounded-full
                            @if ($typeColor === 'blue')    bg-[#e1e6ed] text-[#0f1720]
                            @elseif ($typeColor === 'amber') bg-amber-100 text-amber-700
                            @else bg-[#edf0f4] text-[#586573] @endif">
                            {{ $typeLabel }}
                        </span>

                        @if ($document->visibility === \App\Enums\DocumentVisibility::Landlord)
                            <span class="absolute top-2 right-2 text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                Gedeeld
                            </span>
                        @endif

                        <div class="p-3 flex flex-col gap-2">
                            <p class="text-xs font-medium text-[#0f1720] truncate" title="{{ $document->name }}">
                                {{ $document->name }}
                            </p>
                            @if ($document->visibility === \App\Enums\DocumentVisibility::Landlord)
                                <p class="flex items-center gap-1 text-xs text-[#586573]">
                                    <x-heroicon-o-eye class="w-3 h-3 shrink-0" /> Gedeeld met verhuurder
                                </p>
                            @elseif ($document->visibility === \App\Enums\DocumentVisibility::Building)
                                <p class="flex items-start gap-1 text-xs text-[#586573]">
                                    <x-heroicon-o-building-office class="w-3 h-3 shrink-0 mt-0.5" />
                                    <span class="min-w-0 break-words">Gedeeld met gebouw{{ $document->building ? ' ' . $document->building->name : '' }}</span>
                                </p>
                            @elseif ($document->visibility === \App\Enums\DocumentVisibility::User && $document->sharedWithUser)
                                <p class="flex items-start gap-1 text-xs text-[#586573]">
                                    <x-heroicon-o-user class="w-3 h-3 shrink-0 mt-0.5" />
                                    <span class="min-w-0 break-words">Gedeeld met {{ $document->sharedWithUser->full_name }}</span>
                                </p>
                            @endif
                            @if ($ocrStatus === \App\Models\Document::OCR_PENDING || $ocrStatus === \App\Models\Document::OCR_PROCESSING)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                                    Wordt geanalyseerd…
                                </span>
                            @elseif ($ocrStatus === \App\Models\Document::OCR_FAILED)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 w-fit">
                                    Analyse mislukt
                                </span>
                            @endif
                            @if ($document->description)
                                <div
                                    x-data="{ open: false, overflowing: false }"
                                    x-init="$nextTick(() => overflowing = $refs.desc.scrollHeight > $refs.desc.clientHeight + 2)"
                                >
                                    <p x-ref="desc" :class="{ 'line-clamp-3': ! open }"
                                        class="text-xs italic text-gray-400 dark:text-gray-500">
                                        {{ $document->description }}
                                    </p>
                                    <button type="button" x-show="overflowing" x-on:click="open = ! open"
                                        class="mt-0.5 text-[11px] font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                        <span x-text="open ? 'Minder tonen' : 'Meer tonen'"></span>
                                    </button>
                                </div>
                            @endif
                            @if ($document->rentalPeriod)
                                <p class="text-xs text-[#9aa6b4] truncate">
                                    Kamer {{ $document->rentalPeriod->room->room_number }}
                                </p>
                            @endif
                            <div class="flex items-center gap-1 mt-1">
                                <button
                                    wire:click="mountAction('editDocument', { documentId: {{ $document->id }} })"
                                    class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-[#0f1720] hover:border-[#0f17201f] hover:bg-[#edf0f4] transition-colors"
                                >
                                    <x-heroicon-o-pencil-square class="w-3 h-3" />
                                </button>
                                <button
                                    wire:click="deleteDocument({{ $document->id }})"
                                    wire:confirm="Ben je zeker dat je dit document wil verwijderen?"
                                    class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-colors"
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
            <div class="bg-white rounded-xl border border-[#0f17201f] overflow-hidden divide-y divide-[#0f17201f]">
                @foreach ($documents as $document)
                    @php
                        $media     = $document->getFirstMedia('document');
                        $thumb     = ($media?->hasGeneratedConversion('thumbnail'))
                                        ? $document->getFirstMediaUrl('document', 'thumbnail')
                                        : null;
                        $url       = route('documents.download', $document);
                        $isPdf     = $media?->mime_type === 'application/pdf';
                        $typeLabel = $this::getTypeLabel($document->type);
                        $typeColor = $this::getTypeColor($document->type);
                        $ocrStatus = $document->ocr_status;
                    @endphp

                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-[#edf0f4] transition-colors">
                        <a href="{{ $url }}" target="_blank" class="flex-shrink-0 w-10 h-14 bg-[#edf0f4] rounded-[4px] overflow-hidden border border-[#0f17201f]">
                            @if ($thumb)
                                <img src="{{ route('documents.download', ['document' => $document, 'conversion' => 'thumbnail']) }}" alt="" class="w-full h-full object-cover" />
                            @elseif ($isPdf)
                                <div class="w-full h-full flex items-center justify-center text-[#9aa6b4]">
                                    <x-heroicon-o-document-text class="w-5 h-5" />
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#9aa6b4]">
                                    <x-heroicon-o-photo class="w-5 h-5" />
                                </div>
                            @endif
                        </a>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-[#0f1720] truncate">
                                {{ $document->name }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs px-1.5 py-0.5 rounded-full
                                    @if ($typeColor === 'blue')    bg-[#e1e6ed] text-[#0f1720]
                                    @elseif ($typeColor === 'amber') bg-amber-100 text-amber-700
                                    @else bg-[#edf0f4] text-[#586573] @endif">
                                    {{ $typeLabel }}
                                </span>
                                @if ($document->rentalPeriod)
                                    <span class="text-xs text-[#9aa6b4]">
                                        Kamer {{ $document->rentalPeriod->room->room_number }}
                                    </span>
                                @endif
                                <span class="text-xs text-[#9aa6b4]">
                                    {{ $document->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                            @if ($document->visibility === \App\Enums\DocumentVisibility::Landlord)
                                <p class="flex items-center gap-1 text-xs text-[#586573] mt-1">
                                    <x-heroicon-o-eye class="w-3 h-3 shrink-0" /> Gedeeld met verhuurder
                                </p>
                            @elseif ($document->visibility === \App\Enums\DocumentVisibility::Building)
                                <p class="flex items-start gap-1 text-xs text-[#586573] mt-1">
                                    <x-heroicon-o-building-office class="w-3 h-3 shrink-0 mt-0.5" />
                                    <span class="min-w-0 break-words">Gedeeld met gebouw{{ $document->building ? ' ' . $document->building->name : '' }}</span>
                                </p>
                            @elseif ($document->visibility === \App\Enums\DocumentVisibility::User && $document->sharedWithUser)
                                <p class="flex items-start gap-1 text-xs text-[#586573] mt-1">
                                    <x-heroicon-o-user class="w-3 h-3 shrink-0 mt-0.5" />
                                    <span class="min-w-0 break-words">Gedeeld met {{ $document->sharedWithUser->full_name }}</span>
                                </p>
                            @endif
                            @if ($ocrStatus === \App\Models\Document::OCR_PENDING || $ocrStatus === \App\Models\Document::OCR_PROCESSING)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 mt-1 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                                    Wordt geanalyseerd…
                                </span>
                            @elseif ($ocrStatus === \App\Models\Document::OCR_FAILED)
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 mt-1 w-fit">
                                    Analyse mislukt
                                </span>
                            @endif
                            @if ($document->description)
                                <div class="mt-1"
                                    x-data="{ open: false, overflowing: false }"
                                    x-init="$nextTick(() => overflowing = $refs.desc.scrollHeight > $refs.desc.clientHeight + 2)"
                                >
                                    <p x-ref="desc" :class="{ 'line-clamp-3': ! open }"
                                        class="text-xs italic text-gray-400 dark:text-gray-500">
                                        {{ $document->description }}
                                    </p>
                                    <button type="button" x-show="overflowing" x-on:click="open = ! open"
                                        class="mt-0.5 text-[11px] font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                        <span x-text="open ? 'Minder tonen' : 'Meer tonen'"></span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button
                                wire:click="mountAction('editDocument', { documentId: {{ $document->id }} })"
                                class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-[#0f1720] hover:border-[#0f17201f] hover:bg-[#edf0f4] transition-colors">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                            <a href="{{ $url }}" target="_blank"
                                class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-[#0f1720] hover:border-[#0f17201f] hover:bg-[#edf0f4] transition-colors">
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                            </a>
                            <button
                                wire:click="deleteDocument({{ $document->id }})"
                                wire:confirm="Ben je zeker dat je dit document wil verwijderen?"
                                class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-colors">
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

    {{-- ===== GEDEELD MET MIJ ===== --}}
    @php $sharedWithMe = $this->getSharedWithMe(); @endphp
    @if ($sharedWithMe->isNotEmpty())
        <section class="mt-8">
            <h2 class="text-base font-semibold text-[#0f1720] mb-3 flex items-center gap-2">
                <x-heroicon-o-share class="w-5 h-5 text-[#9aa6b4]" />
                Gedeeld met mij
                <span class="text-sm font-normal text-[#9aa6b4]">({{ $sharedWithMe->count() }})</span>
            </h2>
            <div class="bg-white rounded-xl border border-[#0f17201f] overflow-hidden divide-y divide-[#0f17201f]">
                @foreach ($sharedWithMe as $doc)
                    <div class="flex items-center gap-4 px-4 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-[#0f1720] truncate">{{ $doc->name }}</p>
                            <p class="text-xs text-[#9aa6b4]">
                                {{ $this::getTypeLabel($doc->type) }} ·
                                van {{ $doc->user->full_name }} ·
                                {{ $doc->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <a href="{{ route('documents.download', $doc) }}" target="_blank"
                            class="p-1.5 rounded-[4px] border border-[#0f17201f] text-[#9aa6b4] hover:text-[#0f1720] hover:bg-[#edf0f4] transition-colors">
                            <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</x-filament-panels::page>
