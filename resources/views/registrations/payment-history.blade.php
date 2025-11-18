<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Registration Information -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-900">Payment History</h3>
                            <a href="{{ route('registrations.show', $registration) }}" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Registration
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-semibold text-gray-700">Event:</span>
                                <span class="text-gray-600">{{ $registration->event->title }}</span>
                            </div>
                            
                            <div>
                                <span class="font-semibold text-gray-700">Registration ID:</span>
                                <span class="text-gray-600">#{{ $registration->id }}</span>
                            </div>
                            
                            <div>
                                <span class="font-semibold text-gray-700">Payment Amount:</span>
                                <span class="text-gray-600">৳{{ number_format($registration->payment_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Current Payment Status -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Current Payment Status</h4>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $registration->getPaymentStatusBadgeClass() }}">
                                            {{ ucfirst($registration->payment_status) }}
                                        </span>
                                        
                                        @if($registration->payment_status === 'pending')
                                            <span class="text-yellow-600 text-sm">Under Review</span>
                                        @elseif($registration->payment_status === 'verified')
                                            <span class="text-green-600 text-sm">Verified Successfully</span>
                                        @elseif($registration->payment_status === 'rejected')
                                            <span class="text-red-600 text-sm">Requires Resubmission</span>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        @if($registration->payment_method)
                                        <div>
                                            <span class="font-medium text-gray-700">Payment Method:</span>
                                            <span class="text-gray-600 ml-1">{{ ucfirst(str_replace('_', ' ', $registration->payment_method)) }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($registration->transaction_id)
                                        <div>
                                            <span class="font-medium text-gray-700">Transaction ID:</span>
                                            <span class="text-gray-600 ml-1">{{ $registration->transaction_id }}</span>
                                        </div>
                                        @endif
                                        
                                        @if($registration->payment_date)
                                        <div>
                                            <span class="font-medium text-gray-700">Payment Date:</span>
                                            <span class="text-gray-600 ml-1">{{ $registration->payment_date->format('F j, Y') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($registration->payment_status === 'rejected' && $registration->user_id === auth()->id())
                                <div class="ml-4">
                                    <a href="{{ route('registrations.payment.resubmit', $registration) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Resubmit Payment
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment Activity Timeline -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Payment Activity Timeline</h4>
                        
                        @if($auditLogs->count() > 0)
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($auditLogs as $index => $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index < $auditLogs->count() - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @if($log->action === 'payment_approved' || $log->action === 'payment_bulk_approved')
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </span>
                                                @elseif($log->action === 'payment_rejected')
                                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                                @else
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                                @endif
                                            </div>
                                            
                                            <div class="min-w-0 flex-1 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">
                                                        <span class="font-medium">
                                                            @if($log->action === 'payment_approved')
                                                                Payment approved
                                                            @elseif($log->action === 'payment_bulk_approved')
                                                                Payment Bulk Approved
                                                            @elseif($log->action === 'payment_rejected')
                                                                Payment Rejected
                                                            @else
                                                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                            @endif
                                                        </span>
                                                        by {{ $log->admin_name }}
                                                    </p>
                                                    
                                                    @if($log->notes)
                                                    <div class="mt-2 text-sm text-gray-600 bg-gray-50 p-3 rounded-md">
                                                        <p class="font-medium text-gray-700 mb-1">Admin Notes:</p>
                                                        <p class="whitespace-pre-line">{{ $log->notes }}</p>
                                                    </div>
                                                    @endif
                                                    
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($log->created_at)->format('F j, Y g:i A') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                                
                                <!-- Initial submission -->
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                </span>
                                            </div>
                                            
                                            <div class="min-w-0 flex-1 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">
                                                        <span class="font-medium">Payment Details Submitted</span>
                                                        by {{ $registration->user->name }}
                                                    </p>
                                                    
                                                    <p class="mt-2 text-xs text-gray-500">
                                                        {{ $registration->registered_at->format('F j, Y g:i A') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500 border border-gray-200 rounded-lg">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p>No payment activity recorded yet.</p>
                            <p class="text-sm">Payment verification activities will appear here.</p>
                        </div>
                        @endif
                    </div>

                    <!-- Payment Guidelines -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h5 class="font-semibold text-blue-800 mb-3">Payment Guidelines</h5>
                        <div class="text-sm text-blue-700 space-y-2">
                            <p><strong>Payment Verification Process:</strong></p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Payment details are reviewed by our admin team</li>
                                <li>Verification typically takes 1-2 business days</li>
                                <li>You'll receive email notifications for status updates</li>
                                <li>If rejected, you can resubmit with correct information</li>
                            </ul>
                            
                            <p class="mt-4"><strong>Common Rejection Reasons:</strong></p>
                            <ul class="list-disc list-inside space-y-1 ml-4">
                                <li>Incorrect transaction ID or reference number</li>
                                <li>Payment amount doesn't match required fee</li>
                                <li>Payment date is outside acceptable range</li>
                                <li>Incomplete or unclear payment information</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>