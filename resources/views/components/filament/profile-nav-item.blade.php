@props(['user' => auth()->user()])

<div class="w-full pb-4 pt-3 border-t border-[#0f17201f]">
    <a
        href="{{ route('filament.dashboard.pages.profile') }}"
        class="flex flex-col items-center gap-3 rounded-lg px-3 py-4 cursor-pointer transition-colors duration-150 hover:bg-[#edf0f4]"
    >
        <img
            src="{{ \Filament\Facades\Filament::getUserAvatarUrl($user) }}"
            alt="{{ $user->full_name }}"
            class="w-16 h-16 rounded-full object-cover ring-2 ring-[#0f17201f]"
        />
        <span class="text-xs font-medium text-[#586573] truncate w-full text-center">
            {{ $user->full_name }}
        </span>
    </a>
</div>
