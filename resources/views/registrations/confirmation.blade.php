<x-page-layout>
    <x-slot name="title">Registration Confirmation - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Registration Confirmed!</h1>
            <p class="siks-body text-white/90">
                Your registration for {{ $registration->event->title }} has been submitted successfully
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                <!-- Success Message -->
                <div class="siks-card p-6 mb-8 bg-green-50 border border-green-200">
                    <div class="flex items-start">
                        <svg class="w-8 h-8 text-green-600 mr-4 mt-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h2 class="siks-heading-3 text-green-800 mb-3">Registration Submitted Successfully!</h2>
                            <div class="siks-body text-green-700 space-y-2">
                                <p>Thank you for registering for <strong>{{ $registration->event->title }}</strong>. Your registration has been submitted and is currently under review.</p>
                                @if($registration->payment_required)
                                    <p>Since this event requires payment, your registration will be approved once the payment is verified by our admin team.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Summary -->
                <div class="mb-8">
                    <h2 class="siks-heading-2 mb-6">Registration Summary</h2>
                    
                    <div class="siks-card p-6 bg-gray-50">
                        <div class="siks-grid-2 gap-6">
                            <div>
                                <h3 class="siks-heading-4 mb-4">Event Details</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="siks-body font-medium text-gray-700">Event:</span>
                                        <span class="siks-body text-gray-600">{{ $registration->event->title }}</span>
                                    </div>
                                        
                                        @if($registration->event->fest)
                                        <div>
                                            <span class="font-medium text-gray-700">Fest:</span>
                                            <span class="text-gray-600">{{ $registration->event->fest->title }}</span>
                                        </div>
                                        @endif
                                        
                                        <div>
                                            <span class="font-medium text-gray-700">Date:</span>
                                            <span class="text-gray-600">{{ $registration->event->event_date->format('F j, Y') }}</span>
                                        </div>
                                        
                                        @if($registration->event->event_time)
                                        <div>
                                            <span class="font-medium text-gray-700">Time:</span>
                                            <span class="text-gray-600">{{ $registration->event->event_time->format('g:i A') }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($registration->event->location)
                                        <div>
                                            <span class="font-medium text-gray-700">Location:</span>
                                            <span class="text-gray-600">{{ $registration->event->location }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-3">Registration Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">Registration ID:</span>
                                            <span class="text-gray-600">#{{ $registration->id }}</span>
                                        </div>
                                        
                                        <div>
                                            <span class="font-medium text-gray-700">Participant:</span>
                                            <span class="text-gray-600">{{ $registration->getParticipantName() }}</span>
                                        </div>
                                        
                                        <div>
                                            <span class="font-medium text-gray-700">Type:</span>
                                            <span class="text-gray-600 capitalize">{{ $registration->registration_type }}</span>
                                        </div>
                                        
                                        <div>
                                            <span class="font-medium text-gray-700">Status:</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->getStatusBadgeClass() }}">
                                                {{ ucfirst($registration->status) }}
                                            </span>
                                        </div>
                                        
                                        <div>
                                            <span class="font-medium text-gray-700">Registered At:</span>
                                            <span class="text-gray-600">{{ $registration->registered_at->format('F j, Y g:i A') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    @if($registration->payment_required)
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Payment Information</h3>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-yellow-800">Payment Verification Pending</h4>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>Your payment details have been submitted and are currently being verified by our admin team.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-yellow-800">Amount:</span>
                                    <span class="text-yellow-700">৳{{ number_format($registration->payment_amount, 2) }}</span>
                                </div>
                                
                                @if($registration->payment_method)
                                <div>
                                    <span class="font-medium text-yellow-800">Payment Method:</span>
                                    <span class="text-yellow-700 capitalize">{{ str_replace('_', ' ', $registration->payment_method) }}</span>
                                </div>
                                @endif
                                
                                @if($registration->transaction_id)
                                <div>
                                    <span class="font-medium text-yellow-800">Transaction ID:</span>
                                    <span class="text-yellow-700">{{ $registration->transaction_id }}</span>
                                </div>
                                @endif
                                
                                @if($registration->payment_date)
                                <div>
                                    <span class="font-medium text-yellow-800">Payment Date:</span>
                                    <span class="text-yellow-700">{{ $registration->payment_date->format('F j, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Next Steps -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">What's Next?</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <div class="space-y-3 text-sm text-blue-800">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p><strong>Confirmation Email:</strong> You will receive a confirmation email shortly with your registration details.</p>
                                    </div>
                                </div>
                                
                                @if($registration->payment_required)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p><strong>Payment Verification:</strong> Our admin team will verify your payment details within 24-48 hours.</p>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p><strong>Approval Notification:</strong> You will be notified via email once your registration is approved.</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p><strong>Event Reminders:</strong> You will receive event reminders and any important updates via email.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <div class="flex space-x-4">
                            <a href="{{ route('events.show', $registration->event) }}" 
                               class="text-sm text-gray-600 hover:text-gray-900">
                                ← Back to Event
                            </a>
                            
                            <a href="{{ route('events.index') }}" 
                               class="text-sm text-gray-600 hover:text-gray-900">
                                Browse More Events
                            </a>
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('registrations.show', $registration) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View Registration Details
                            </a>
                            
                            <a href="{{ route('registrations.history') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View All My Registrations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>