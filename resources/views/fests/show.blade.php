<x-page-layout>
    <x-slot name="title">{{ $fest->title }} - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <div class="flex justify-center items-center gap-4 mb-4">
                @auth
                    @if(auth()->user()->canManageFests())
                        <span class="siks-badge-darker">
                            {{ ucfirst($fest->status) }}
                        </span>
                    @endif
                @endauth
            </div>
            <h1 class="siks-heading-1 text-white mb-4">{{ $fest->title }}</h1>
            <div class="flex justify-center items-center gap-6 text-white/90 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">
                        {{ $fest->start_date->format('M j, Y') }}
                        @if($fest->start_date->format('Y-m-d') !== $fest->end_date->format('Y-m-d'))
                            - {{ $fest->end_date->format('M j, Y') }}
                        @endif
                    </span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                    <span>Organized by {{ $fest->creator->name }}</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    @if(auth()->user()->canManageFests())
                        <a href="{{ route('fests.edit', $fest) }}" class="siks-btn-base bg-white text-siks-darker hover:bg-gray-100 focus:ring-white">
                            Edit Fest
                        </a>
                    @endif
                @endauth
                <a href="{{ route('fests.index') }}" class="siks-btn-outline border-white text-white hover:bg-white hover:text-siks-darker">
                    ← Back to Fests
                </a>
            </div>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
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

            <!-- Fest Description -->
            @if($fest->banner_image || $fest->description)
                <div class="siks-card mb-8">
                    @if($fest->banner_image)
                        <div class="aspect-w-16 aspect-h-6">
                            <img src="{{ asset('storage/' . $fest->banner_image) }}" 
                                 alt="{{ $fest->title }}" 
                                 class="w-full h-64 object-cover">
                        </div>
                    @endif
                    
                    @if($fest->description)
                        <div class="p-6">
                            <h2 class="siks-heading-3 mb-4">About This Fest</h2>
                            <div class="prose max-w-none">
                                <p class="siks-body text-gray-700 leading-relaxed">{{ $fest->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Fest Gallery -->
            <div class="mb-8">
                <x-gallery-widget 
                    :images="$fest->gallery ?? collect()" 
                    type="fest" 
                    title="Fest Gallery"
                    :showViewAll="true" />
            </div>

            <!-- Events Section -->
            <div class="siks-card">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h2 class="siks-heading-2 mb-2">
                                Events ({{ $fest->events->count() }})
                            </h2>
                            <p class="siks-body text-gray-600">
                                All events under this fest
                            </p>
                        </div>
                        @auth
                            @if(auth()->user()->canManageFests())
                                <a href="{{ route('events.create', ['fest_id' => $fest->id]) }}" class="siks-btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Event
                                </a>
                            @endif
                        @endauth
                    </div>

                    @if($fest->events->count() > 0)
                        <div class="siks-grid-1 gap-6">
                            @foreach($fest->events as $event)
                                <x-event-card :event="$event" :show-fest="false" />
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="siks-heading-3 mb-4">No Events Yet</h3>
                            <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                                Get started by adding the first event to this fest.
                            </p>
                            @auth
                                @if(auth()->user()->canManageFests())
                                    <a href="{{ route('events.create', ['fest_id' => $fest->id]) }}" class="siks-btn-primary">
                                        Add First Event
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-section>
</x-page-layout>