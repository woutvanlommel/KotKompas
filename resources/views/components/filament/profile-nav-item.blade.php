{{-- resources/views/components/filament/profile-nav-item.blade.php --}}
@props(['user' => auth()->user()])

<div class="p-4 border-t border-gray-200 dark:border-white/10">
    <a href="{{ \App\Filament\Dashboard\Pages\Profile::getUrl() }}" class="flex items-center gap-3 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors p-2 -mx-2">
        <x-filament::avatar :src="$user->avatar_url" />
        <span>{{ $user->name }}</span>
    </a>
</div>
