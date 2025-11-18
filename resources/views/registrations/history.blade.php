<x-page-layout>
    <x-slot name="title">My Registrations - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">My Registrations</h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                View and manage all your event registrations in one place.
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-6xl mx-auto">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="siks-heading-2 mb-2">Registration History</h2>
                    <p class="siks-body text-gray-600">
                        {{ $registrations->total() }} {{ Str::plural('registration', $registrations->total()) }} found
                    </p>
                </div>
                <a href="{{ route('events.index') }}" class="siks-btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Browse Events
                </a>
            </div>

            @if($registrations->count() > 0)
                <!-- Registrations List -->
                <div class="space-y-6 mb-8">
                    @foreach($registrations as $registration)
                        <div class="siks-card p-6 hover:shadow-lg transition-shadow">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                                <!-- Registration Info -->
                                <div class="flex-1">
                                    <!-- Header -->
                                    <div class="flex flex-wrap items-center gap-3 mb-4">
                                        <h3 class="siks-heading-4">{{ $registration->event->title }}</h3>
                                        
                                        <!-- Status Badge -->
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-gray-100 text-gray-800'
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$registration->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($registration->status) }}
                                        </span>
                                        
                                        <!-- Payment Status -->
                                        @if($registration->payment_required)
                                            @php
                                                $paymentColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'verified' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800'
                                                ];
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $paymentColors[$registration->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                                Payment: {{ ucfirst($registration->payment_status) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <span class="siks-body-small text-gray-500">Event Date</span>
                                            <p class="siks-body font-medium">{{ $registration->event->event_date->format('M j, Y') }}</p>
                                        </div>
                                        
                                        @if($registration->event->fest)
                                        <div>
                                            <span class="siks-body-small text-gray-500">Fest</span>
                                            <p class="siks-body font-medium">{{ $registration->event->fest->title }}</p>
                                        </div>
                                        @endif
                                        
                                        <div>
                                            <span class="siks-body-small text-gray-500">Registered On</span>
                                            <p class="siks-body font-medium">{{ $registration->registered_at->format('M j, Y') }}</p>
                                        </div>
                                        
                                        @if($registration->event->location)
                                        <div>
                                            <span class="siks-body-small text-gray-500">Location</span>
                                            <p class="siks-body font-medium">{{ $registration->event->location }}</p>
                                        </div>
                                        @endif
                                        
                                        <div>
                                            <span class="siks-body-small text-gray-500">Registration Type</span>
                                            <p class="siks-body font-medium">{{ ucfirst($registration->registration_type) }}</p>
                                        </div>
                                        
                                        @if($registration->payment_required)
                                        <div>
                                            <span class="siks-body-small text-gray-500">Fee</span>
                                            <p class="siks-body font-medium">৳{{ number_format($registration->payment_amount, 2) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Team Info -->
                                    @if($registration->registration_type === 'team' && $registration->team_name)
                                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                                        <span class="siks-body-small text-blue-600 font-medium">Team: </span>
                                        <span class="siks-body text-blue-800">{{ $registration->team_name }}</span>
                                        @if($registration->team_members_json)
                                            <span class="siks-body-small text-blue-600">({{ $registration->getTeamMemberCount() + 1 }} members)</span>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    <!-- Admin Notes -->
                                    @if($registration->admin_notes && $registration->isRejected())
                                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <span class="siks-body-small text-red-600 font-medium">Admin Notes: </span>
                                        <span class="siks-body text-red-800">{{ $registration->admin_notes }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex flex-row lg:flex-col gap-2 lg:min-w-[140px]">
                                    <a href="{{ route('registrations.show', $registration) }}" class="siks-btn-outline text-sm">
                                        View Details
                                    </a>
                                    
                                    <a href="{{ route('events.show', $registration->event) }}" class="siks-btn-ghost text-sm">
                                        View Event
                                    </a>
                                    
                                    @if($registration->isPending() && !$registration->isCancelled() && $registration->event->event_date > now()->addDay())
                                    <form method="POST" action="{{ route('registrations.cancel', $registration) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to cancel this registration?')"
                                                class="siks-btn-outline text-sm text-red-600 border-red-300 hover:bg-red-50 w-full">
                                            Cancel
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $registrations->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-3 mb-4">No Registrations Yet</h3>
                    <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                        You haven't registered for any events yet. Browse our upcoming events and join the activities that interest you.
                    </p>
                    <a href="{{ route('events.index') }}" class="siks-btn-primary">
                        Browse Events
                    </a>
                </div>
            @endif
        </div>
    </x-section>
</x-page-layout>