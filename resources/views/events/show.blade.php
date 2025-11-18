<x-page-layout>
    <x-slot name="title">{{ $event->title }} - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">{{ $event->title }}</h1>
            @if($event->fest)
                <div class="mb-4">
                    <span class="siks-badge-darker">{{ $event->fest->title }}</span>
                </div>
            @endif
            <p class="siks-body text-white/90">
                <span class="capitalize">{{ str_replace('_', ' ', $event->type) }}</span> • 
                By {{ $event->author->name ?? 'Unknown' }}
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-6xl mx-auto">
            <div class="siks-grid-2 gap-8">
                <!-- Left Column: Event Details -->
                <div>
                    <div class="siks-card p-6 mb-6">
                        <h2 class="siks-heading-3 mb-6">Event Details</h2>
                        
                        <div class="space-y-6">
                            <div>
                                <h4 class="siks-heading-4 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-siks-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Date & Time
                                </h4>
                                <p class="siks-body">{{ $event->event_date->format('l, F j, Y') }}</p>
                                <p class="siks-body">{{ $event->event_time ? $event->event_time->format('g:i A') : 'Time TBA' }}</p>
                            </div>

                            @if($event->location)
                            <div>
                                <h4 class="siks-heading-4 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-siks-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Location
                                </h4>
                                <p class="siks-body">{{ $event->location }}</p>
                            </div>
                            @endif

                            <div>
                                <h4 class="siks-heading-4 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-siks-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                    </svg>
                                    Description
                                </h4>
                                <div class="siks-body whitespace-pre-line">{{ $event->description }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Image -->
                    @if ($event->image)
                        <div class="siks-card p-6 mb-6">
                            <h4 class="siks-heading-4 mb-4">Event Image</h4>
                            <img src="{{ asset('storage/' . $event->image) }}" class="w-full h-64 object-cover rounded-lg" alt="{{ $event->title }}">
                        </div>
                    @endif
                </div>

                <!-- Right Column: Registration Info -->
                <div>
                    <div class="siks-card p-6 mb-6">
                        <h3 class="siks-heading-3 mb-6">Registration Information</h3>
                        
                        <div class="space-y-4">
                            <!-- Registration Type -->
                            <div class="flex justify-between items-center">
                                <span class="siks-body font-medium">Registration Type:</span>
                                <span class="siks-badge-primary capitalize">
                                    {{ str_replace(['_', 'both'], [' ', 'Individual & Team'], $event->registration_type) }}
                                </span>
                            </div>

                            <!-- Capacity Info -->
                            @if($event->max_participants)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="siks-body font-medium">Capacity:</span>
                                    <span class="siks-body">
                                        {{ $event->getRegisteredCount() }} / {{ $event->max_participants }} registered
                                    </span>
                                </div>
                                @if($event->getAvailableSpots() !== null)
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-siks-primary h-2 rounded-full transition-all duration-300" style="width: {{ ($event->getRegisteredCount() / $event->max_participants) * 100 }}%"></div>
                                    </div>
                                @endif
                            </div>
                            @endif

                            <!-- Fee Info -->
                            <div class="flex justify-between items-center">
                                <span class="siks-body font-medium">Registration Fee:</span>
                                <span class="siks-body font-semibold text-siks-primary">
                                    @if($event->fee_amount > 0)
                                        ৳{{ number_format($event->fee_amount, 2) }}
                                    @else
                                        Free
                                    @endif
                                </span>
                            </div>

                            <!-- Registration Deadline -->
                            @if($event->registration_deadline)
                            <div class="flex justify-between items-center">
                                <span class="siks-body font-medium">Registration Deadline:</span>
                                <span class="siks-body text-orange-600">
                                    {{ $event->registration_deadline->format('M j, Y g:i A') }}
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Registration Status & Buttons -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            @if($event->registration_type === 'on_spot')
                                <div class="siks-card p-4 bg-blue-50 border border-blue-200 text-center">
                                    <p class="font-semibold text-blue-800">On-Spot Registration</p>
                                    <p class="text-sm text-blue-600">Register at the venue on event day</p>
                                </div>
                            @elseif(!$event->isRegistrationOpen())
                                @if($event->isFull())
                                    <div class="siks-card p-4 bg-red-50 border border-red-200 text-center">
                                        <p class="font-semibold text-red-800">Event Full</p>
                                        <p class="text-sm text-red-600">Maximum capacity reached</p>
                                    </div>
                                @elseif($event->registration_deadline && $event->registration_deadline < now())
                                    <div class="siks-card p-4 bg-yellow-50 border border-yellow-200 text-center">
                                        <p class="font-semibold text-yellow-800">Registration Closed</p>
                                        <p class="text-sm text-yellow-600">Deadline has passed</p>
                                    </div>
                                @else
                                    <div class="siks-card p-4 bg-gray-50 border border-gray-200 text-center">
                                        <p class="font-semibold text-gray-800">Registration Not Available</p>
                                    </div>
                                @endif
                            @else
                                @auth
                                    @if($userRegistration)
                                        <div class="siks-card p-4 bg-green-50 border border-green-200 text-center mb-4">
                                            <p class="font-semibold text-green-800">You are registered!</p>
                                            <p class="text-sm text-green-600">Status: {{ ucfirst($userRegistration->status) }}</p>
                                            @if($userRegistration->registration_type === 'team' && $userRegistration->team_name)
                                                <p class="text-sm text-green-600">Team: {{ $userRegistration->team_name }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            @if($event->allowsIndividualRegistration())
                                                <a href="{{ route('registrations.individual', $event) }}" class="siks-btn-primary w-full justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    Register as Individual
                                                </a>
                                            @endif
                                            @if($event->allowsTeamRegistration())
                                                <a href="{{ route('registrations.team', $event) }}" class="siks-btn-outline w-full justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    Register as Team
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="siks-card p-4 bg-gray-50 border border-gray-200 text-center">
                                        <p class="font-semibold text-gray-800 mb-2">Login Required</p>
                                        <p class="text-sm text-gray-600 mb-4">Please login to register for this event</p>
                                        <a href="{{ route('login') }}" class="siks-btn-primary">Login</a>
                                    </div>
                                @endauth
                            @endif
                        </div>
                    </div>

                    <!-- Event Gallery -->
                    @if($event->gallery && $event->gallery->count() > 0)
                        <div class="siks-card p-6">
                            <x-gallery-widget 
                                :images="$event->gallery" 
                                type="event" 
                                title="Event Gallery"
                                :showViewAll="true" />
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-4 mt-8 pt-8 border-t border-gray-200">
                <a href="{{ route('events.index') }}" class="siks-btn-ghost">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Events
                </a>
                
                @auth
                    @can('update', $event)
                        <a href="{{ route('events.edit', $event) }}" class="siks-btn-outline">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Event
                        </a>
                    @endcan
                    
                    @can('delete', $event)
                        <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500" onclick="return confirm('Delete this event?')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Event
                            </button>
                        </form>
                    @endcan
                @endauth
            </div>
        </div>
    </x-section>
</x-page-layout>
