<x-pulse::card :cols="$cols ?? 4" :rows="$rows ?? 4" :class="$class ?? ''">
    <x-pulse::card-header name="Defender Security">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Z" clip-rule="evenodd" />
            </svg>
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand ?? false" wire:poll.10s="">
        @if (! $hasTable)
            <x-pulse::no-results message="Run the defender migrations to enable database logging." />
        @else
            <div class="grid grid-cols-2 gap-3 p-4">
                <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $recentThreats }}</div>
                    <div class="text-xs text-red-500 dark:text-red-300 mt-1">Threats (last hour)</div>
                </div>
                <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-3 text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $totalThreats }}</div>
                    <div class="text-xs text-orange-500 dark:text-orange-300 mt-1">Total threats</div>
                </div>
            </div>

            @if ($topIps->isNotEmpty())
                <div class="px-4 pb-2">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Top IPs</div>
                    @foreach ($topIps as $row)
                        <div class="flex items-center justify-between py-1 text-sm">
                            <span class="font-mono text-gray-700 dark:text-gray-300">{{ $row->ip }}</span>
                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/40 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">
                                {{ $row->total }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($latestThreats->isNotEmpty())
                <div class="px-4 pb-4">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Recent detections</div>
                    @foreach ($latestThreats as $threat)
                        <div class="border-l-2 border-red-300 dark:border-red-700 pl-3 py-1 mb-2">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-mono text-gray-700 dark:text-gray-300">{{ $threat->ip }}</span>
                                @if ($threat->country_code)
                                    <span class="text-gray-400">{{ $threat->country_code }}</span>
                                @endif
                                <span class="ml-auto text-gray-400 dark:text-gray-500">{{ $threat->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                {{ $threat->reason ?? $threat->route }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-pulse::no-results message="No threats detected recently." />
            @endif
        @endif
    </x-pulse::scroll>
</x-pulse::card>
