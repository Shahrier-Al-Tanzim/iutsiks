@props(['event', 'showFest' => true])

<div class="siks-card siks-card-hover">
    <!-- Event Image -->
    @if($event->image)
        <div class="h-48 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $event->image) }}')"></div>
    @else
        <div class="h-48 bg-gradient-to-br from-siks-primary to-siks-darker flex items-center justify-center">
            <span class="text-white text-lg font-medium">{{ ucfirst($event->type) }}</span>
        </div>
    @endif

    <div class="p-6">
        <!-- Event Header -->
        <div class="mb-4">
            @if($showFest && $event->fest)
                <span class="bg-siks-darker text-white px-3 py-1 rounded-full text-xs mb-2 inline-block">
                    {{ $event->fest->title }}
                </span>
            @endif
            <h3 class="siks-heading-4 mb-2">
                <a href="{{ route('events.show', $event) }}" class="hover:text-siks-darker transition-colors">
                    {{ $event->title }}
                </a>
            </h3>
            <p class="siks-body-small">
                <span class="capitalize">{{ str_replace('_', ' ', $event->type) }}</span>
                @if($event->location)
                    • {{ $event->location }}
                @endif
            </p>
        </div>

        <!-- Event Details -->
        <div class="mb-4 space-y-3">
            <div class="flex items-center siks-body-small">
                <svg class="w-4 h-4 mr-2 text-siks-darker" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
                {{ $event->event_date->format('M j, Y') }}
                @if($event->event_time)
                    • {{ $event->event_time->format('g:i A') }}
                @endif
            </div>

            <!-- Registration Info -->
            <div class="flex items-center siks-body-small">
                <svg class="w-4 h-4 mr-2 text-siks-darker" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                </svg>
                @if($event->registration_type === 'on_spot')
                    <span class="text-blue-600">On-spot registration</span>
                @else
                    <span class="capitalize">{{ str_replace(['_', 'both'], [' ', 'Individual & Team'], $event->registration_type) }}</span>
                    @if($event->max_participants)
                        • {{ $event->getRegisteredCount() }}/{{ $event->max_participants }}
                    @endif
                @endif
            </div>

            <!-- Fee Info -->
            <div class="flex items-center siks-body-small">
                <svg class="w-4 h-4 mr-2 text-siks-darker" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                </svg>
                @if($event->fee_amount > 0)
                    <span class="text-orange-600 font-medium">৳{{ number_format($event->fee_amount, 2) }}</span>
                @else
                    <span class="text-siks-darker font-medium">Free</span>
                @endif
            </div>
        </div>

        <!-- Registration Status -->
        <div class="mb-4 flex flex-wrap gap-2">
            @if($event->registration_type === 'on_spot')
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">On-Spot Registration</span>
            @elseif(!$event->isRegistrationOpen())
                @if($event->isFull())
                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">Full</span>
                @elseif($event->registration_deadline && $event->registration_deadline < now())
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">Registration Closed</span>
                @else
                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium">Registration Unavailable</span>
                @endif
            @else
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">Registration Open</span>
            @endif

            <!-- Status Badge -->
            @auth
                @if(auth()->user()->canManageEvents())
                    @if($event->status === 'draft')
                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium">Draft</span>
                    @elseif($event->status === 'completed')
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-medium">Completed</span>
                    @endif
                @endif
            @endauth
        </div>

        <!-- Capacity Bar -->
        @if($event->max_participants && $event->registration_type !== 'on_spot')
            <div class="mb-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-siks-darker h-2 rounded-full transition-all duration-300" style="width: {{ min(100, ($event->getRegisteredCount() / $event->max_participants) * 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $event->getRegisteredCount() }} / {{ $event->max_participants }} registered
                </p>
            </div>
        @endif

        <!-- Author Info -->
        <div class="mb-4 siks-body-small text-gray-500">
            By {{ $event->author->name ?? 'Unknown' }}
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('events.show', $event) }}" class="siks-btn-primary flex-1 text-center">
                View Details
            </a>
            
            @auth
                @can('update', $event)
                    <a href="{{ route('events.edit', $event) }}" class="siks-btn-base bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500">
                        Edit
                    </a>
                @endcan
                
                @can('delete', $event)
                    <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="siks-btn-base bg-red-500 text-white hover:bg-red-600 focus:ring-red-500" onclick="return confirm('Delete this event?')">
                            Delete
                        </button>
                    </form>
                @endcan
            @endauth
        </div>
    </div>
</div>