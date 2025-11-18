<x-page-layout>
    <x-slot name="title">Prayer Times - {{ $requestedDate->format('F j, Y') }} - SIKS</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Prayer Times</h1>
            <p class="siks-body text-white/90">{{ $requestedDate->format('F j, Y') }}</p>
        </div>
    </x-section>

    <!-- Content Section -->
    <x-section>
        <div class="max-w-6xl mx-auto">
            <div class="siks-card p-8">
                @if($prayerTimes)
                    <!-- Date and Location Header -->
                    <div class="text-center mb-8">
                        <h2 class="siks-heading-2 mb-2">{{ $formattedTimes['date'] }}</h2>
                        <p class="siks-body text-gray-600">{{ $formattedTimes['location'] }}</p>
                    </div>

                    <!-- Prayer Times Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                        @foreach($formattedTimes['prayers'] as $prayer)
                            <div class="bg-gradient-to-br from-siks-primary/5 to-siks-primary/10 rounded-xl p-6 text-center border border-siks-primary/20 hover:shadow-lg transition-shadow">
                                <div class="w-12 h-12 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-siks-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <h3 class="siks-heading-4 mb-3 text-siks-secondary">{{ $prayer['name'] }}</h3>
                                <p class="text-2xl font-bold text-siks-primary">{{ $prayer['formatted'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Additional Information -->
                    @if($prayerTimes->notes)
                        <div class="bg-siks-primary/5 border border-siks-primary/20 rounded-lg p-6 mb-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-siks-primary mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="siks-heading-4 text-siks-primary mb-2">Notes</h4>
                                    <p class="siks-body text-gray-700">{{ $prayerTimes->notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Last Updated Info -->
                    <div class="text-center border-t border-gray-200 pt-6 mb-6">
                        <p class="siks-body-small text-gray-500">
                            Last updated by <span class="font-medium text-gray-700">{{ $prayerTimes->updatedBy->name }}</span> 
                            on {{ $prayerTimes->updated_at->format('F j, Y \a\t g:i A') }}
                        </p>
                    </div>
                @else
                    <!-- No Prayer Times Available -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="siks-heading-3 mb-3">No Prayer Times Available</h3>
                        <p class="siks-body text-gray-600 mb-6">
                            Prayer times for {{ $requestedDate->format('F j, Y') }} have not been set yet.
                        </p>
                        @can('manage-prayer-times')
                            <a href="{{ route('admin.prayer-times.edit', $requestedDate->format('Y-m-d')) }}" 
                               class="siks-btn-primary">
                                Set Prayer Times
                            </a>
                        @endcan
                    </div>
                @endif

                <!-- Navigation -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('prayer-times.show', $requestedDate->copy()->subDay()->format('Y-m-d')) }}" 
                       class="siks-btn-ghost w-full sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous Day
                    </a>
                    
                    <a href="{{ route('prayer-times.index') }}" 
                       class="siks-btn-primary w-full sm:w-auto">
                        Today
                    </a>
                    
                    <a href="{{ route('prayer-times.show', $requestedDate->copy()->addDay()->format('Y-m-d')) }}" 
                       class="siks-btn-ghost w-full sm:w-auto">
                        Next Day
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </x-section>
</x-page-layout>