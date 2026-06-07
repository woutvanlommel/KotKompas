<x-filament-panels::page>
    <div class="flex items-center gap-4 mb-6">
        @php $user = auth()->user(); @endphp

        @if ($user->avatar)
            <img
                src="{{ Storage::url($user->avatar) }}"
                alt="{{ $user->full_name }}"
                class="w-20 h-20 rounded-full object-cover"
            />
        @else
            <div class="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-2xl font-semibold text-gray-500 dark:text-gray-300">
                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->lastname, 0, 1)) }}
            </div>
        @endif

        <div>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $user->full_name }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
        </div>
    </div>

    {{ $this->form }}
</x-filament-panels::page>
