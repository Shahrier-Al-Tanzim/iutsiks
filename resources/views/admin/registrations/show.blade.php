<x-page-layout>
    <x-slot name="title">Registration Details - Admin - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Registration Details</h1>
            <p class="siks-body text-white/90">
                Admin view for registration #{{ $registration->id }}
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-6xl mx-auto">
            <div class="siks-card p-6">
                <!-- Header with Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h2 class="siks-heading-2 mb-2">Registration #{{ $registration->id }}</h2>
                        <p class="siks-body text-gray-600">Admin management view</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('admin.registrations.index') }}" class="siks-btn-ghost">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to All Registrations
                        </a>
                        
                        @if($registration->payment_required && $registration->payment_status === 'pending')
                        <a href="{{ route('admin.registrations.payment-verification') }}" class="siks-btn-outline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Payment Verification
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Registration Status Banner -->
                <div class="mb-8">
                    @if($registration->isApproved())
                        <div class="siks-card p-4 bg-green-50 border border-green-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h3 class="siks-heading-4 text-green-800 mb-2">Registration Approved</h3>
                                    <p class="siks-body text-green-700">This registration has been approved and the participant has been notified.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($registration->isPending())
                        <div class="siks-card p-4 bg-yellow-50 border border-yellow-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h3 class="siks-heading-4 text-yellow-800 mb-2">Registration Pending Review</h3>
                                    <p class="siks-body text-yellow-700">
                                        This registration is awaiting admin approval.
                                        @if($registration->needsPaymentVerification())
                                            Payment verification is also pending.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($registration->isRejected())
                        <div class="siks-card p-4 bg-red-50 border border-red-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <h3 class="siks-heading-4 text-red-800 mb-2">Registration Rejected</h3>
                                    <p class="siks-body text-red-700">This registration has been rejected.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Admin Actions -->
                @if($registration->status === 'pending')
                <div class="siks-card p-6 mb-8 bg-blue-50 border border-blue-200">
                    <h3 class="siks-heading-3 text-blue-800 mb-4">Admin Actions</h3>
                    <div class="flex flex-col sm:flex-row gap-3">
                        @if($registration->canBeApproved())
                        <button type="button" 
                                onclick="openApproveModal()"
                                class="siks-btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve Registration
                        </button>
                        @else
                        <div class="siks-card p-3 bg-gray-50 border border-gray-200">
                            <p class="siks-body-small text-gray-600">
                                @if($registration->payment_required && $registration->payment_status !== 'verified')
                                    Payment must be verified before registration can be approved.
                                @endif
                            </p>
                        </div>
                        @endif
                        
                        <button type="button" 
                                onclick="openRejectModal()"
                                class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject Registration
                        </button>
                    </div>
                </div>
                @endif

                <!-- Payment Actions -->
                @if($registration->payment_required && $registration->payment_status === 'pending')
                <div class="siks-card p-6 mb-8 bg-yellow-50 border border-yellow-200">
                    <h3 class="siks-heading-3 text-yellow-800 mb-4">Payment Verification</h3>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" 
                                onclick="openApprovePaymentModal()"
                                class="siks-btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve Payment
                        </button>
                        
                        <button type="button" 
                                onclick="openRejectPaymentModal()"
                                class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject Payment
                        </button>
                    </div>
                </div>
                @endif

                <!-- Include the rest of the registration details from the user view -->
                @include('registrations.show', ['registration' => $registration, 'hideActions' => true])
            </div>
        </div>
    </x-section>

    <!-- Approve Registration Modal -->
    <div id="approveModal" class="siks-modal hidden">
        <div class="siks-modal-content">
            <div class="mt-3">
                <h3 class="siks-heading-3 mb-4">Approve Registration</h3>
                <form method="POST" action="{{ route('admin.registrations.approve', $registration) }}" class="space-y-4">
                    @csrf
                    <div class="siks-form-group">
                        <label class="siks-label" for="admin_notes">Admin Notes (Optional)</label>
                        <textarea id="admin_notes" name="admin_notes" rows="3" class="siks-textarea"
                                  placeholder="Add any notes about this approval..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApproveModal()" class="siks-btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="siks-btn-primary">
                            Approve Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Registration Modal -->
    <div id="rejectModal" class="siks-modal hidden">
        <div class="siks-modal-content">
            <div class="mt-3">
                <h3 class="siks-heading-3 mb-4">Reject Registration</h3>
                <form method="POST" action="{{ route('admin.registrations.reject', $registration) }}" class="space-y-4">
                    @csrf
                    <div class="siks-form-group">
                        <label class="siks-label" for="rejection_reason">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required class="siks-textarea"
                                  placeholder="Please provide a reason for rejecting this registration..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()" class="siks-btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500">
                            Reject Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Payment Modal -->
    <div id="approvePaymentModal" class="siks-modal hidden">
        <div class="siks-modal-content">
            <div class="mt-3">
                <h3 class="siks-heading-3 mb-4">Approve Payment</h3>
                <form method="POST" action="{{ route('admin.registrations.approve-payment', $registration) }}" class="space-y-4">
                    @csrf
                    <div class="siks-form-group">
                        <label class="siks-label" for="payment_admin_notes">Admin Notes (Optional)</label>
                        <textarea id="payment_admin_notes" name="admin_notes" rows="3" class="siks-textarea"
                                  placeholder="Add any notes about this payment approval..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApprovePaymentModal()" class="siks-btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="siks-btn-primary">
                            Approve Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Payment Modal -->
    <div id="rejectPaymentModal" class="siks-modal hidden">
        <div class="siks-modal-content">
            <div class="mt-3">
                <h3 class="siks-heading-3 mb-4">Reject Payment</h3>
                <form method="POST" action="{{ route('admin.registrations.reject-payment', $registration) }}" class="space-y-4">
                    @csrf
                    <div class="siks-form-group">
                        <label class="siks-label" for="payment_rejection_reason">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea id="payment_rejection_reason" name="rejection_reason" rows="3" required class="siks-textarea"
                                  placeholder="Please provide a clear reason for rejecting this payment..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectPaymentModal()" class="siks-btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500">
                            Reject Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openApproveModal() {
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
            document.getElementById('admin_notes').value = '';
        }

        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
        }

        function openApprovePaymentModal() {
            document.getElementById('approvePaymentModal').classList.remove('hidden');
        }

        function closeApprovePaymentModal() {
            document.getElementById('approvePaymentModal').classList.add('hidden');
            document.getElementById('payment_admin_notes').value = '';
        }

        function openRejectPaymentModal() {
            document.getElementById('rejectPaymentModal').classList.remove('hidden');
        }

        function closeRejectPaymentModal() {
            document.getElementById('rejectPaymentModal').classList.add('hidden');
            document.getElementById('payment_rejection_reason').value = '';
        }

        // Close modals when clicking outside
        ['approveModal', 'rejectModal', 'approvePaymentModal', 'rejectPaymentModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', function(e) {
                if (e.target === this) {
                    if (modalId === 'approveModal') closeApproveModal();
                    else if (modalId === 'rejectModal') closeRejectModal();
                    else if (modalId === 'approvePaymentModal') closeApprovePaymentModal();
                    else if (modalId === 'rejectPaymentModal') closeRejectPaymentModal();
                }
            });
        });
    </script>
</x-page-layout>