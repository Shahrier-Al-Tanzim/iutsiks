<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Resubmit Payment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Payment Rejection Notice -->
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Payment Verification Failed</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>Your previous payment submission could not be verified. Please review the information below and resubmit your payment details.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Information -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $registration->event->title }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700">Registration ID:</span>
                                <span class="text-gray-600">#{{ $registration->id }}</span>
                            </div>
                            
                            <div>
                                <span class="font-semibold text-gray-700">Required Amount:</span>
                                <span class="text-gray-600">৳{{ number_format($registration->payment_amount, 2) }}</span>
                            </div>
                            
                            <div>
                                <span class="font-semibold text-gray-700">Event Date:</span>
                                <span class="text-gray-600">{{ $registration->event->event_date->format('F j, Y') }}</span>
                            </div>
                            
                            @if($registration->event->event_time)
                            <div>
                                <span class="font-semibold text-gray-700">Event Time:</span>
                                <span class="text-gray-600">{{ $registration->event->event_time->format('g:i A') }}</span>
                            </div>
                            @endif
                            
                            @if($registration->registration_type === 'team')
                            <div>
                                <span class="font-semibold text-gray-700">Team Name:</span>
                                <span class="text-gray-600">{{ $registration->team_name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Previous Payment Details -->
                    @if($registration->transaction_id)
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h4 class="font-semibold text-yellow-800 mb-2">Previous Payment Submission</h4>
                        <div class="text-sm text-yellow-700 space-y-1">
                            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $registration->payment_method)) }}</p>
                            <p><strong>Transaction ID:</strong> {{ $registration->transaction_id }}</p>
                            <p><strong>Payment Date:</strong> {{ $registration->payment_date->format('F j, Y') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Admin Notes -->
                    @if($registration->admin_notes)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">Admin Notes</h4>
                        <div class="text-sm text-blue-700 whitespace-pre-line">{{ $registration->admin_notes }}</div>
                    </div>
                    @endif

                    <!-- Payment Resubmission Form -->
                    <div class="bg-white">
                        <h4 class="text-xl font-semibold text-gray-900 mb-6">Resubmit Payment Details</h4>
                        
                        <form method="POST" action="{{ route('registrations.payment.resubmit.store', $registration) }}" class="space-y-6">
                            @csrf
                            
                            <!-- Instructions -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h5 class="font-semibold text-blue-800 mb-2">Payment Instructions</h5>
                                <div class="text-sm text-blue-700">
                                    <p class="mb-2">Please ensure the following before resubmitting:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Payment amount matches exactly: <strong>৳{{ number_format($registration->payment_amount, 2) }}</strong></li>
                                        <li>Transaction ID is correct and complete</li>
                                        <li>Payment date is accurate</li>
                                        <li>Payment method is clearly specified</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Payment Method -->
                            <div>
                                <x-input-label for="payment_method" :value="__('Payment Method')" />
                                <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="bkash" {{ old('payment_method', $registration->payment_method) == 'bkash' ? 'selected' : '' }}>bKash</option>
                                    <option value="nagad" {{ old('payment_method', $registration->payment_method) == 'nagad' ? 'selected' : '' }}>Nagad</option>
                                    <option value="bank_transfer" {{ old('payment_method', $registration->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cash" {{ old('payment_method', $registration->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                            </div>
                            
                            <!-- Transaction ID -->
                            <div>
                                <x-input-label for="transaction_id" :value="__('Transaction ID / Reference Number')" />
                                <x-text-input id="transaction_id" 
                                              name="transaction_id" 
                                              type="text" 
                                              class="mt-1 block w-full" 
                                              :value="old('transaction_id', $registration->transaction_id)" 
                                              placeholder="Enter correct transaction ID or reference number"
                                              required />
                                <p class="mt-1 text-sm text-gray-600">
                                    Double-check this information from your payment receipt or transaction history.
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('transaction_id')" />
                            </div>
                            
                            <!-- Payment Date -->
                            <div>
                                <x-input-label for="payment_date" :value="__('Payment Date')" />
                                <x-text-input id="payment_date" 
                                              name="payment_date" 
                                              type="date" 
                                              class="mt-1 block w-full" 
                                              :value="old('payment_date', $registration->payment_date ? $registration->payment_date->format('Y-m-d') : '')" 
                                              max="{{ date('Y-m-d') }}"
                                              required />
                                <p class="mt-1 text-sm text-gray-600">
                                    Enter the exact date when you made the payment.
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
                            </div>

                            <!-- Additional Notes -->
                            <div>
                                <x-input-label for="additional_notes" :value="__('Additional Notes (Optional)')" />
                                <textarea id="additional_notes" 
                                          name="additional_notes" 
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Any additional information about your payment that might help with verification">{{ old('additional_notes') }}</textarea>
                                <p class="mt-1 text-sm text-gray-600">
                                    You can provide any additional context that might help us verify your payment.
                                </p>
                            </div>

                            <!-- Confirmation -->
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="confirm_accuracy" name="confirm_accuracy" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" required>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="confirm_accuracy" class="font-medium text-gray-700">
                                            I confirm that all payment details are accurate
                                        </label>
                                        <p class="text-gray-500">
                                            I have double-checked all the information and confirm that the payment details provided are correct and complete.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <div class="flex space-x-3">
                                    <a href="{{ route('registrations.show', $registration) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                        ← Back to Registration
                                    </a>
                                    
                                    <a href="{{ route('registrations.payment.history', $registration) }}" class="text-sm text-gray-600 hover:text-gray-900">
                                        View Payment History
                                    </a>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <x-secondary-button type="button" onclick="window.history.back()">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>
                                    
                                    <x-primary-button>
                                        {{ __('Resubmit Payment Details') }}
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