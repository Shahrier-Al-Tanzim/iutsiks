@php
    $fests = \App\Models\Fest::orderBy('title')->get();
    $eventTypes = ['quiz', 'lecture', 'donation', 'competition', 'workshop'];
    $registrationStatuses = ['open', 'closed', 'full'];
@endphp

<x-page-layout>
    <x-slot name="title">Events - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Events</h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                Discover upcoming events, workshops, lectures, and competitions organized by SIKS.
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filters -->
            <div class="siks-card p-6 mb-8">
                <form method="GET" action="{{ route('events.index') }}" class="space-y-4 lg:space-y-0 lg:grid lg:grid-cols-5 lg:gap-4">
                    <!-- Fest Filter -->
                    <div>
                        <label for="fest_id" class="block text-sm font-medium text-gray-700 mb-2">Fest</label>
                        <select name="fest_id" id="fest_id" class="siks-input">
                            <option value="">All Fests</option>
                            @foreach($fests as $fest)
                                <option value="{{ $fest->id }}" {{ request('fest_id') == $fest->id ? 'selected' : '' }}>
                                    {{ $fest->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" id="type" class="siks-input">
                            <option value="">All Types</option>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Registration Status Filter -->
                    <div>
                        <label for="registration_status" class="block text-sm font-medium text-gray-700 mb-2">Registration</label>
                        <select name="registration_status" id="registration_status" class="siks-input">
                            <option value="">All Status</option>
                            <option value="open" {{ request('registration_status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ request('registration_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="full" {{ request('registration_status') == 'full' ? 'selected' : '' }}>Full</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label for="date_filter" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <select name="date_filter" id="date_filter" class="siks-input">
                            <option value="">All Dates</option>
                            <option value="upcoming" {{ request('date_filter') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="past" {{ request('date_filter') == 'past' ? 'selected' : '' }}>Past</option>
                            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="siks-btn-primary">
                            Filter
                        </button>
                        <a href="{{ route('events.index') }}" class="siks-btn-ghost">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="siks-heading-2 mb-2">
                        @if(request()->hasAny(['fest_id', 'type', 'registration_status', 'date_filter']))
                            Filtered Events
                        @else
                            All Events
                        @endif
                    </h2>
                    <p class="siks-body text-gray-600">
                        {{ $events->total() }} {{ Str::plural('event', $events->total()) }} found
                    </p>
                </div>
                @can('create', App\Models\Event::class)
                    <a href="{{ route('events.create') }}" class="siks-btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Event
                    </a>
                @endcan
            </div>

            <!-- Events Grid -->
            @if($events->count() > 0)
                <div class="siks-grid-3 mb-8">
                    @foreach($events as $event)
                        <x-event-card :event="$event" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $events->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-3 mb-4">
                        @if(request()->hasAny(['fest_id', 'type', 'registration_status', 'date_filter']))
                            No Events Match Your Filters
                        @else
                            No Events Yet
                        @endif
                    </h3>
                    <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                        @if(request()->hasAny(['fest_id', 'type', 'registration_status', 'date_filter']))
                            Try adjusting your filters or <a href="{{ route('events.index') }}" class="text-siks-primary hover:underline">clear all filters</a> to see more events.
                        @else
                            We haven't scheduled any events yet. Check back soon for exciting upcoming events and activities.
                        @endif
                    </p>
                    @can('create', App\Models\Event::class)
                        <a href="{{ route('events.create') }}" class="siks-btn-primary">
                            Create the First Event
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </x-section>
</x-page-layout>
