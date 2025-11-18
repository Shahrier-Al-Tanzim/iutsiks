<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Register for Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Event Information -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $event->title }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700">Date:</span>
                                <span class="text-gray-600">{{ $event->event_date->format('F j, Y') }}</span>
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
                        <h4 class="text-xl font-semibold text-gray-900 mb-6">Individual Registration</h4>
                        
                        <form method="POST" action="{{ route('registrations.individual.store', $event) }}" class="space-y-6">
                            @csrf
                            
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h5 class="text-lg font-medium text-gray-900">Personal Information</h5>
                                
                                <div>
                                    <x-input-label for="individual_name" :value="__('Full Name')" />
                                    <x-text-input id="individual_name" 
                                                  name="individual_name" 
                                                  type="text" 
                                                  class="mt-1 block w-full" 
                                                  :value="old('individual_name', auth()->user()->name)" 
                                                  required />
                                    <x-input-error class="mt-2" :messages="$errors->get('individual_name')" />
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="email" :value="__('Email')" />
                                        <x-text-input id="email" 
                                                      name="email" 
                                                      type="email" 
                                                      class="mt-1 block w-full bg-gray-100" 
                                                      :value="auth()->user()->email" 
                                                      readonly />
                                    </div>
                                    
                                    <div>
                                        <x-input-label for="phone" :value="__('Phone Number')" />
                                        <x-text-input id="phone" 
                                                      name="phone" 
                                                      type="text" 
                                                      class="mt-1 block w-full bg-gray-100" 
                                                      :value="auth()->user()->phone ?? 'Not provided'" 
                                                      readonly />
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
                            <div class="space-y-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <h5 class="text-lg font-medium text-gray-900">Payment Information</h5>
                                
                                <div class="bg-yellow-100 border border-yellow-300 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">
                                                Payment Required: ৳{{ number_format($event->fee_amount, 2) }}
                                            </h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>Please complete your payment before submitting this registration. Your registration will be pending until payment is verified by our admin team.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                                    <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="bkash" {{ old('payment_method') == 'bkash' ? 'selected' : '' }}>bKash</option>
                                        <option value="nagad" {{ old('payment_method') == 'nagad' ? 'selected' : '' }}>Nagad</option>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                                </div>
                                
                                <div>
                                    <x-input-label for="transaction_id" :value="__('Transaction ID / Reference Number')" />
                                    <x-text-input id="transaction_id" 
                                                  name="transaction_id" 
                                                  type="text" 
                                                  class="mt-1 block w-full" 
                                                  :value="old('transaction_id')" 
                                                  placeholder="Enter transaction ID or reference number"
                                                  required />
                                    <x-input-error class="mt-2" :messages="$errors->get('transaction_id')" />
                                </div>
                                
                                <div>
                                    <x-input-label for="payment_date" :value="__('Payment Date')" />
                                    <x-text-input id="payment_date" 
                                                  name="payment_date" 
                                                  type="date" 
                                                  class="mt-1 block w-full" 
                                                  :value="old('payment_date', date('Y-m-d'))" 
                                                  max="{{ date('Y-m-d') }}"
                                                  required />
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
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
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <a href="{{ route('events.show', $event) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    ← Back to Event
                                </a>
                                
                                <div class="flex space-x-3">
                                    <x-secondary-button type="button" onclick="window.history.back()">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>
                                    
                                    <x-primary-button>
                                        {{ __('Submit Registration') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>