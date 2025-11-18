<x-page-layout>
    <x-slot name="title">Prayer Times - SIKS</x-slot>
    
    @if($todaysPrayerTimes)
        <!-- Page Header -->
        <x-section background="primary" padding="medium">
            <div class="text-center">
                <h1 class="siks-heading-1 text-white mb-4">Prayer Times</h1>
                <p class="siks-body text-white/90 mb-2">{{ $formattedTimes['location'] }}</p>
                <p class="siks-body text-white/80">{{ $formattedTimes['date'] }}</p>
            </div>
        </x-section>

        <!-- Current/Next Prayer Highlight -->
        @if($currentPrayer || $nextPrayer)
            <x-section background="gray">
                <div class="max-w-2xl mx-auto">
                    <div class="siks-card p-8 text-center border-2 border-siks-primary">
                        @if($currentPrayer)
                            <h2 class="siks-heading-3 text-siks-primary mb-4">Current Prayer Time</h2>
                            <p class="siks-heading-2 text-gray-900 mb-2">
                                {{ $currentPrayer['name'] }} - {{ $currentPrayer['formatted_time'] }}
                            </p>
                            @if(isset($currentPrayer['time_remaining']))
                                <p class="siks-body text-gray-600">
                                    Time remaining: {{ $currentPrayer['time_remaining'] }}
                                </p>
                            @endif
                        @elseif($nextPrayer)
                            <h2 class="siks-heading-3 text-siks-primary mb-4">Next Prayer</h2>
                            <p class="siks-heading-2 text-gray-900 mb-2">
                                {{ $nextPrayer['name'] }} - {{ $nextPrayer['formatted_time'] }}
                            </p>
                            @if(isset($nextPrayer['time_until']))
                                <p class="siks-body text-gray-600">
                                    In {{ $nextPrayer['time_until'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </x-section>
        @endif

        <!-- Prayer Times Grid -->
        <x-section>
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    @foreach($formattedTimes['prayers'] as $key => $prayer)
                        <div class="siks-card p-6 text-center 
                            {{ ($currentPrayer && strtolower($currentPrayer['name']) === $key) ? 'border-2 border-siks-primary bg-green-50' : (
                               ($nextPrayer && strtolower($nextPrayer['name']) === $key) ? 'border-2 border-blue-500 bg-blue-50' : '') }}">
                            <h3 class="siks-heading-4 mb-3 
                                {{ ($currentPrayer && strtolower($currentPrayer['name']) === $key) ? 'text-siks-primary' : (
                                   ($nextPrayer && strtolower($nextPrayer['name']) === $key) ? 'text-blue-600' : 'text-gray-800') }}">
                                {{ $prayer['name'] }}
                            </h3>
                            <p class="text-2xl font-bold mb-2 
                                {{ ($currentPrayer && strtolower($currentPrayer['name']) === $key) ? 'text-siks-primary' : (
                                   ($nextPrayer && strtolower($nextPrayer['name']) === $key) ? 'text-blue-600' : 'text-gray-900') }}">
                                {{ $prayer['formatted'] }}
                            </p>
                            @if($currentPrayer && strtolower($currentPrayer['name']) === $key)
                                <span class="inline-block px-3 py-1 bg-siks-primary text-white text-xs font-medium rounded-full">
                                    Current
                                </span>
                            @elseif($nextPrayer && strtolower($nextPrayer['name']) === $key)
                                <span class="inline-block px-3 py-1 bg-blue-500 text-white text-xs font-medium rounded-full">
                                    Next
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Additional Information -->
                @if($todaysPrayerTimes->notes)
                    <div class="mt-8 max-w-2xl mx-auto">
                        <div class="siks-card p-6 border-l-4 border-blue-500 bg-blue-50">
                            <h4 class="siks-heading-4 text-blue-800 mb-2">Notes:</h4>
                            <p class="siks-body text-blue-700">{{ $todaysPrayerTimes->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Navigation -->
                <div class="flex flex-col sm:flex-row justify-between items-center mt-12 gap-4">
                    <a href="{{ route('prayer-times.show', now()->subDay()->format('Y-m-d')) }}" 
                       class="siks-btn-outline">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Yesterday
                    </a>
                    
                    <span class="siks-body text-gray-600 font-medium">
                        Today's Prayer Times
                    </span>
                    
                    <a href="{{ route('prayer-times.show', now()->addDay()->format('Y-m-d')) }}" 
                       class="siks-btn-outline">
                        Tomorrow
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </x-section>
    @else
        <!-- No Prayer Times Available -->
        <x-section>
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="siks-heading-1 mb-4">Prayer Times</h1>
                <h3 class="siks-heading-3 mb-4">No Prayer Times Available</h3>
                <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                    Prayer times for today have not been set yet. Please check back later or contact the administration.
                </p>
                @can('manage-prayer-times')
                    <a href="{{ route('admin.prayer-times.edit') }}" class="siks-btn-primary">
                        Set Prayer Times
                    </a>
                @endcan
            </div>
        </x-section>
    @endif
</x-page-layout>