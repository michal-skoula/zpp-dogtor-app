<div wire:poll.3s="checkHealth" wire:init="checkHealth" class="flex items-center gap-2 px-3">
    @php
        $color = match($this->status) {
            'ok'      => 'bg-green-500',
            'dry_run' => 'bg-orange-400',
            default   => 'bg-red-500',
        };
        $label = match($this->status) {
            'ok'      => 'Connected',
            'dry_run' => 'Dry Run',
            default   => 'Disconnected',
        };
    @endphp
    <span class="relative flex h-3 w-3">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $color }} opacity-75"></span>
        <span class="relative inline-flex h-3 w-3 rounded-full {{ $color }}"></span>
    </span>
    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $label }}</span>
</div>
