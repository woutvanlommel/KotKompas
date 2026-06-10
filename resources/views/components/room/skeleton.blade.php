{{-- Skeleton loading state — kalm, lichte versie van de echte layout --}}

{{-- Header: titel + prijs --}}
<div class="flex items-start justify-between gap-6">
    <div class="space-y-3">
        <div class="kk-skeleton h-11 w-96 max-w-[60vw]"></div>
        <div class="kk-skeleton h-4 w-64 max-w-[45vw]"></div>
        <div class="mt-2 flex gap-2">
            <div class="kk-skeleton h-7 w-20 rounded-full"></div>
            <div class="kk-skeleton h-7 w-24 rounded-full"></div>
            <div class="kk-skeleton h-7 w-28 rounded-full"></div>
        </div>
    </div>
    <div class="shrink-0 space-y-2 text-right">
        <div class="kk-skeleton ml-auto h-10 w-36"></div>
        <div class="kk-skeleton ml-auto h-3.5 w-20"></div>
    </div>
</div>

{{-- Gallery --}}
<div class="mt-8 hidden md:grid md:h-[30rem] md:grid-cols-4 md:grid-rows-2 md:gap-2 overflow-hidden rounded-2xl">
    <div class="kk-skeleton col-span-2 row-span-2 rounded-none"></div>
    <div class="kk-skeleton rounded-none"></div>
    <div class="kk-skeleton rounded-none"></div>
    <div class="kk-skeleton rounded-none"></div>
    <div class="kk-skeleton rounded-none"></div>
</div>
<div class="mt-8 kk-skeleton h-72 rounded-2xl md:hidden"></div>

{{-- Beschrijving + kaartje --}}
<div class="mt-16 grid gap-10 lg:grid-cols-[1fr_320px]">
    <div class="space-y-3">
        <div class="kk-skeleton h-3.5 w-24"></div>
        <div class="kk-skeleton h-4 w-full"></div>
        <div class="kk-skeleton h-4 w-full"></div>
        <div class="kk-skeleton h-4 w-5/6"></div>
        <div class="kk-skeleton h-4 w-full"></div>
        <div class="kk-skeleton h-4 w-3/4"></div>
        <div class="kk-skeleton h-4 w-full"></div>
        <div class="kk-skeleton h-4 w-4/5"></div>
    </div>
    <div class="kk-skeleton h-56 rounded-2xl"></div>
</div>

{{-- Faciliteiten --}}
<div class="mt-16 space-y-4">
    <div class="kk-skeleton h-3.5 w-24"></div>
    <div class="kk-skeleton h-7 w-56"></div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="kk-skeleton h-14 rounded-xl"></div>
        <div class="kk-skeleton h-14 rounded-xl"></div>
    </div>
    <div class="flex flex-wrap gap-2 pt-1">
        @for ($i = 0; $i < 5; $i++)
            <div class="kk-skeleton h-9 rounded-xl" style="width: {{ [88, 104, 96, 120, 80][$i] }}px"></div>
        @endfor
    </div>
</div>

{{-- Prijsoverzicht --}}
<div class="mt-16 space-y-4">
    <div class="kk-skeleton h-3.5 w-24"></div>
    <div class="kk-skeleton h-7 w-40"></div>
    <div class="kk-skeleton h-52 rounded-2xl"></div>
</div>
