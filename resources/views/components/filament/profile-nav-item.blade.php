@props(['user' => auth()->user()])

<div class="px-3 pb-4 pt-2 border-t border-gray-200 dark:border-white/10">
    <a
        href="{{ \App\Filament\Dashboard\Pages\Profile::getUrl() }}"
        class="flex flex-row items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium cursor-pointer transition-colors duration-150 hover:bg-gray-100 dark:hover:bg-white/5"
    >
        <x-filament::avatar
            :src="\Filament\Facades\Filament::getUserAvatarUrl($user)"
            :alt="$user->full_name"
            size="sm"
        />
        <span class="truncate text-gray-700 dark:text-gray-200">
            {{ $user->full_name }}
        </span>
    </a>
</div>
