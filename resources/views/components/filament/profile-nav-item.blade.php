@props(['user' => auth()->user()])

<div class="w-full pb-4 pt-2 border-t border-gray-200 dark:border-white/10">
    <a
        href="{{ route('filament.dashboard.pages.profile') }}"
        class="flex flex-col items-center gap-2 rounded-lg px-3 py-3 cursor-pointer transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-white/5"
    >
        <x-filament::avatar
            :src="\Filament\Facades\Filament::getUserAvatarUrl($user)"
            :alt="$user->full_name"
            size="md"
        />
        <span class="text-xs font-medium text-gray-700 dark:text-gray-200 truncate w-full text-center">
            {{ $user->full_name }}
        </span>
    </a>
</div>
