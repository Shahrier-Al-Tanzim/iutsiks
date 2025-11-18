<x-page-layout>
    <x-slot name="title">Fests - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Fests</h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                Discover our exciting festivals and multi-day events that bring the community together.
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="siks-heading-2 mb-2">All Fests</h2>
                    <p class="siks-body text-gray-600">
                        Explore our festivals and special events
                    </p>
                </div>
                @auth
                    @if(auth()->user()->canManageFests())
                        <a href="{{ route('fests.create') }}" class="siks-btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Fest
                        </a>
                    @endif
                @endauth
            </div>
            @if(session('success'))
                <div class="siks-card p-4 mb-6 bg-green-50 border border-green-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-green-800 font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($fests->count() > 0)
                <div class="siks-grid-3 mb-8">
                    @foreach($fests as $fest)
                        <x-fest-card :fest="$fest" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $fests->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-3 mb-4">No Fests Available</h3>
                    <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                        We haven't scheduled any fests yet. Check back later for upcoming festivals and special events.
                    </p>
                    @auth
                        @if(auth()->user()->canManageFests())
                            <a href="{{ route('fests.create') }}" class="siks-btn-primary">
                                Create First Fest
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </x-section>
</x-page-layout>