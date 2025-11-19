<x-page-layout>
    <x-slot name="title">Register for {{ $event->title }} - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Individual Registration</h1>
            <p class="siks-body text-white/90">
                Register for {{ $event->title }}
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                <!-- Event Information -->
                <div class="siks-card p-6 mb-8 bg-gray-50">
                    <h2 class="siks-heading-2 mb-4">{{ $event->title }}</h2>
                        
                    <div class="siks-grid-2 gap-4">
                        <div class="flex justify-between">
                            <span class="siks-body font-medium text-gray-700">Date:</span>
                            <span class="siks-body text-gray-600">{{ $event->event_date->format('F j, Y') }}</span>
                        </div>
                            
                            @if($event->event_time)
                            <div>
                                <span class="font-semibold text-gray-700">Time:</span>
                                <span class="text-gray-600">{{ $event->event_time->format('g:i A') }}</span>
                            </div>
                            @endif
                            
                            @if($event->location)
                            <div>
                                <span class="font-semibold text-gray-700">Location:</span>
                                <span class="text-gray-600">{{ $event->location }}</span>
                            </div>
                            @endif
                            
                            @if($event->fest)
                            <div>
                                <span class="font-semibold text-gray-700">Fest:</span>
                                <span class="text-gray-600">{{ $event->fest->title }}</span>
                            </div>
                            @endif
                            
                            @if($event->requiresPayment())
                            <div>
                                <span class="font-semibold text-gray-700">Registration Fee:</span>
                                <span class="text-gray-600">৳{{ number_format($event->fee_amount, 2) }}</span>
                            </div>
                            @endif
                            
                            @if($availability['max_participants'])
                            <div>
                                <span class="font-semibold text-gray-700">Available Spots:</span>
                                <span class="text-gray-600">{{ $availability['remaining_spots'] }} / {{ $availability['max_participants'] }}</span>
                            </div>
                            @endif
                        </div>
                        
                        @if($event->description)
                        <div class="mt-4">
                            <span class="font-semibold text-gray-700">Description:</span>
                            <p class="text-gray-600 mt-2">{{ $event->description }}</p>
                        </div>
                        @endif
                    </div>

                <!-- Registration Form -->
                <div class="bg-white">
                    <h2 class="siks-heading-2 mb-6">Individual Registration</h2>
                    
                    <form method="POST" action="{{ route('registrations.individual.store', $event) }}" class="space-y-6">
                        @csrf
                        
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="siks-heading-3">Personal Information</h3>
                            
                            <div class="siks-form-group">
                                <label class="siks-label" for="individual_name">Full Name</label>
                                <input id="individual_name" 
                                       name="individual_name" 
                                       type="text" 
                                       class="siks-input" 
                                       value="{{ old('individual_name', auth()->user()->name) }}" 
                                       required>
                                @error('individual_name')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="siks-form-row">
                                <div class="siks-form-group">
                                    <label class="siks-label" for="email">Email</label>
                                    <input id="email" 
                                           name="email" 
                                           type="email" 
                                           class="siks-input bg-gray-100" 
                                           value="{{ auth()->user()->email }}" 
                                           readonly>
                                </div>
                                
                                <div class="siks-form-group">
                                    <label class="siks-label" for="phone">Phone Number</label>
                                    <input id="phone" 
                                           name="phone" 
                                           type="text" 
                                           class="siks-input bg-gray-100" 
                                           value="{{ auth()->user()->phone ?? 'Not provided' }}" 
                                           readonly>
                                </div>
                            </div>
                                
                                @if(auth()->user()->student_id)
                                <div>
                                    <x-input-label for="student_id" :value="__('Student ID')" />
                                    <x-text-input id="student_id" 
                                                  name="student_id" 
                                                  type="text" 
                                                  class="mt-1 block w-full bg-gray-100" 
                                                  :value="auth()->user()->student_id" 
                                                  readonly />
                                </div>
                                @endif
                            </div>

                        <!-- Payment Information (if required) -->
                        @if($event->requiresPayment())
                        <div class="siks-card p-6 bg-yellow-50 border border-yellow-200">
                            <h3 class="siks-heading-3 mb-4">Payment Information</h3>
                            
                            <div class="siks-card p-4 bg-yellow-100 border border-yellow-300 mb-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <h4 class="siks-heading-4 text-yellow-800 mb-2">
                                            Payment Required: ৳{{ number_format($event->fee_amount, 2) }}
                                        </h4>
                                        <p class="siks-body text-yellow-700">
                                            Please complete your payment before submitting this registration. Your registration will be pending until payment is verified by our admin team.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="siks-form-group">
                                <label class="siks-label" for="payment_method">Payment Method</label>
                                <select id="payment_method" name="payment_method" class="siks-select" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="bkash" {{ old('payment_method') == 'bkash' ? 'selected' : '' }}>bKash</option>
                                        <option value="nagad" {{ old('payment_method') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                @error('payment_method')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="siks-form-group">
                                <label class="siks-label" for="transaction_id">Transaction ID / Reference Number</label>
                                <input id="transaction_id" 
                                       name="transaction_id" 
                                       type="text" 
                                       class="siks-input" 
                                       value="{{ old('transaction_id') }}" 
                                       placeholder="Enter transaction ID or reference number"
                                       required>
                                @error('transaction_id')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="siks-form-group">
                                <label class="siks-label" for="payment_date">Payment Date</label>
                                <input id="payment_date" 
                                       name="payment_date" 
                                       type="date" 
                                       class="siks-input" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}"
                                       required>
                                @error('payment_date')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        @endif

                            <!-- Terms and Conditions -->
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="terms" name="terms" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" required>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="terms" class="font-medium text-gray-700">
                                            I agree to the terms and conditions
                                        </label>
                                        <p class="text-gray-500">
                                            By registering, I confirm that all information provided is accurate and I understand the event policies.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('events.show', $event) }}" class="siks-btn-ghost">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Event
                            </a>
                            
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="button" onclick="window.history.back()" class="siks-btn-ghost">
                                    Cancel
                                </button>
                                
                                <button type="submit" class="siks-btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Submit Registration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-section>
</x-page-layout>