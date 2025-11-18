<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registration Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Registration Status Banner -->
                    <div class="mb-6">
                        @if($registration->isApproved())
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Registration Approved</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Your registration has been approved! You're all set for the event.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($registration->isPending())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Registration Pending</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>Your registration is under review. 
                                            @if($registration->needsPaymentVerification())
                                                Payment verification is pending.
                                            @endif
                                            You will be notified once it's approved.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($registration->isRejected())
                            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Registration Rejected</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>Unfortunately, your registration was not approved.</p>
                                            @if($registration->admin_notes)
                                                <p class="mt-1"><strong>Reason:</strong> {{ $registration->admin_notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($registration->isCancelled())
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-gray-800">Registration Cancelled</h3>
                                        <div class="mt-2 text-sm text-gray-700">
                                            <p>This registration has been cancelled.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Event Information -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $registration->event->title }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700">Date:</span>
                                <span class="text-gray-600">{{ $registration->event->event_date->format('F j, Y') }}</span>
                            </div>
                            
                            @if($registration->event->event_time)
                            <div>
                                <span class="font-semibold text-gray-700">Time:</span>
                                <span class="text-gray-600">{{ $registration->event->event_time->format('g:i A') }}</span>
                            </div>
                            @endif
                            
                            @if($registration->event->location)
                            <div>
                                <span class="font-semibold text-gray-700">Location:</span>
                                <span class="text-gray-600">{{ $registration->event->location }}</span>
                            </div>
                            @endif
                            
                            @if($registration->event->fest)
                            <div>
                                <span class="font-semibold text-gray-700">Fest:</span>
                                <span class="text-gray-600">{{ $registration->event->fest->title }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Registration Information -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Registration Details -->
                        <div class="space-y-6">
                            <h4 class="text-xl font-semibold text-gray-900">Registration Details</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="font-semibold text-gray-700">Registration ID:</span>
                                    <span class="text-gray-600">#{{ $registration->id }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-semibold text-gray-700">Registration Type:</span>
                                    <span class="text-gray-600 capitalize">{{ $registration->registration_type }}</span>
                                </div>
                                
                                @if($registration->registration_type === 'team')
                                <div>
                                    <span class="font-semibold text-gray-700">Team Name:</span>
                                    <span class="text-gray-600">{{ $registration->team_name }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-semibold text-gray-700">Team Leader:</span>
                                    <span class="text-gray-600">{{ $registration->user->name }}</span>
                                </div>
                                @else
                                <div>
                                    <span class="font-semibold text-gray-700">Participant Name:</span>
                                    <span class="text-gray-600">{{ $registration->getParticipantName() }}</span>
                                </div>
                                @endif
                                
                                <div>
                                    <span class="font-semibold text-gray-700">Email:</span>
                                    <span class="text-gray-600">{{ $registration->user->email }}</span>
                                </div>
                                
                                @if($registration->user->phone)
                                <div>
                                    <span class="font-semibold text-gray-700">Phone:</span>
                                    <span class="text-gray-600">{{ $registration->user->phone }}</span>
                                </div>
                                @endif
                                
                                @if($registration->user->student_id)
                                <div>
                                    <span class="font-semibold text-gray-700">Student ID:</span>
                                    <span class="text-gray-600">{{ $registration->user->student_id }}</span>
                                </div>
                                @endif
                                
                                <div>
                                    <span class="font-semibold text-gray-700">Registered At:</span>
                                    <span class="text-gray-600">{{ $registration->registered_at->format('F j, Y g:i A') }}</span>
                                </div>
                                
                                <div>
                                    <span class="font-semibold text-gray-700">Status:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->getStatusBadgeClass() }}">
                                        {{ ucfirst($registration->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        @if($registration->payment_required)
                        <div class="space-y-6">
                            <h4 class="text-xl font-semibold text-gray-900">Payment Information</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="font-semibold text-gray-700">Amount:</span>
                                    <span class="text-gray-600">৳{{ number_format($registration->payment_amount, 2) }}</span>
                                </div>
                                
                                @if($registration->payment_method)
                                <div>
                                    <span class="font-semibold text-gray-700">Payment Method:</span>
                                    <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $registration->payment_method) }}</span>
                                </div>
                                @endif
                                
                                @if($registration->transaction_id)
                                <div>
                                    <span class="font-semibold text-gray-700">Transaction ID:</span>
                                    <span class="text-gray-600">{{ $registration->transaction_id }}</span>
                                </div>
                                @endif
                                
                                @if($registration->payment_date)
                                <div>
                                    <span class="font-semibold text-gray-700">Payment Date:</span>
                                    <span class="text-gray-600">{{ $registration->payment_date->format('F j, Y') }}</span>
                                </div>
                                @endif
                                
                                <div>
                                    <span class="font-semibold text-gray-700">Payment Status:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->getPaymentStatusBadgeClass() }}">
                                        {{ ucfirst($registration->payment_status) }}
                                    </span>
                                    @if($registration->payment_status === 'rejected' && $registration->user_id === auth()->id())
                                        <a href="{{ route('registrations.payment.resubmit', $registration) }}" 
                                           class="ml-2 text-sm text-red-600 hover:text-red-900 underline">
                                            Resubmit Payment
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Team Members Section (for team registrations) -->
                    @if($registration->registration_type === 'team')
                    <div class="mt-8">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xl font-semibold text-gray-900">Team Members</h4>
                            @if($registration->user_id === auth()->id() && $registration->status === 'pending')
                            <a href="{{ route('registrations.team.manage', $registration) }}" 
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Manage Team
                            </a>
                            @endif
                        </div>

                        <!-- Team Leader -->
                        <div class="mb-4">
                            <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $registration->user->name }}</p>
                                            <p class="text-sm text-gray-600">{{ $registration->user->email }}</p>
                                        </div>
                                        <div class="text-right">
                                            @if($registration->user->student_id)
                                            <p class="text-sm text-gray-600">ID: {{ $registration->user->student_id }}</p>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Team Leader
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Team Members -->
                        @php
                            $teamMembers = $registration->team_members_json ?? [];
                        @endphp

                        @if(count($teamMembers) > 0)
                        <div class="space-y-3">
                            @foreach($teamMembers as $member)
                            <div class="flex items-center p-4 border border-gray-200 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $member['name'] }}</p>
                                            <p class="text-sm text-gray-600">{{ $member['email'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            @if(isset($member['student_id']) && $member['student_id'])
                                            <p class="text-sm text-gray-600">ID: {{ $member['student_id'] }}</p>
                                            @endif
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                @if($member['status'] === 'accepted') bg-green-100 text-green-800
                                                @elseif($member['status'] === 'pending_invitation') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                @if($member['status'] === 'accepted')
                                                    Accepted
                                                @elseif($member['status'] === 'pending_invitation')
                                                    Pending Invitation
                                                @else
                                                    {{ ucfirst($member['status']) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p>No team members added yet.</p>
                        </div>
                        @endif

                        <!-- Team Summary -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm">
                                <div>
                                    <span class="text-gray-600">Total team size:</span>
                                    <span class="font-medium text-gray-900">{{ count($teamMembers) + 1 }} members</span>
                                </div>
                                @if($registration->payment_required)
                                <div>
                                    <span class="text-gray-600">Total team fee:</span>
                                    <span class="font-medium text-gray-900">৳{{ number_format($registration->payment_amount, 2) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Admin Notes -->
                    @if($registration->admin_notes)
                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                        <h5 class="font-semibold text-blue-900 mb-2">Admin Notes</h5>
                        <p class="text-blue-800 text-sm">{{ $registration->admin_notes }}</p>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="flex space-x-3">
                            <a href="{{ route('events.show', $registration->event) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                ← Back to Event
                            </a>
                            
                            <a href="{{ route('registrations.history') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                View All Registrations
                            </a>
                            
                            @if($registration->payment_required)
                            <a href="{{ route('registrations.payment.history', $registration) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                Payment History
                            </a>
                            @endif
                        </div>
                        
                        @if($registration->user_id === auth()->id() && $registration->isPending() && !$registration->isCancelled())
                        <form method="POST" action="{{ route('registrations.cancel', $registration) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <x-danger-button type="submit" 
                                           onclick="return confirm('Are you sure you want to cancel this registration? This action cannot be undone.')"
                                           class="ml-3">
                                {{ __('Cancel Registration') }}
                            </x-danger-button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>