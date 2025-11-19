<x-page-layout>
    <x-slot name="title">Payment Verification - Admin - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Payment Verification</h1>
            <p class="siks-body text-white/90">
                Review and verify payment submissions from participants
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
            <div class="siks-card p-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h2 class="siks-heading-2 mb-2">Pending Payment Verifications</h2>
                        <p class="siks-body text-gray-600">Review and verify payment submissions from participants</p>
                    </div>
                    <a href="{{ route('admin.registrations.index') }}" class="siks-btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to All Registrations
                    </a>
                </div>

                <!-- Filters -->
                @if($events->count() > 0)
                <div class="siks-card p-4 mb-6">
                    <form method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                        <div class="flex-1">
                            <label class="siks-label" for="event_id">Filter by Event</label>
                            <select name="event_id" id="event_id" class="siks-select">
                                <option value="">All Events</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                        {{ $event->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="siks-btn-primary">Filter</button>
                            <a href="{{ route('admin.registrations.payment-verification') }}" class="siks-btn-ghost">Clear</a>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Bulk Actions -->
                @if($pendingPayments->count() > 0)
                <div class="siks-card p-4 mb-6 bg-yellow-50 border border-yellow-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="siks-heading-4 text-yellow-800 mb-1">Bulk Actions</h3>
                            <p class="siks-body-small text-yellow-700">Select multiple payments to approve them at once</p>
                        </div>
                        <button type="button" onclick="openBulkApproveModal()" 
                                class="siks-btn-primary disabled:opacity-50"
                                id="bulkApproveBtn" disabled>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve Selected (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
                @endif

                <!-- Pending Payments List -->
                @if($pendingPayments->count() > 0)
                <div class="space-y-6">
                    @foreach($pendingPayments as $registration)
                    <div class="siks-card p-6">
                        <div class="space-y-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0">
                                            <input type="checkbox" 
                                                   class="payment-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                   value="{{ $registration->id }}"
                                                   onchange="updateBulkActions()">
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <!-- Registration Info -->
                                            <div class="flex items-center space-x-2 mb-3">
                                                <h4 class="text-lg font-medium text-gray-900">
                                                    {{ $registration->getParticipantName() }}
                                                </h4>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ ucfirst($registration->registration_type) }}
                                                </span>
                                                @if($registration->registration_type === 'team')
                                                <span class="text-sm text-gray-500">
                                                    ({{ $registration->getTeamMemberCount() + 1 }} members)
                                                </span>
                                                @endif
                                            </div>
                                            
                                            <!-- Event Info -->
                                            <div class="text-sm text-gray-600 mb-4">
                                                <p><strong>Event:</strong> {{ $registration->event->title }}</p>
                                                <p><strong>Participant:</strong> {{ $registration->user->name }} ({{ $registration->user->email }})</p>
                                                <p><strong>Registration ID:</strong> #{{ $registration->id }}</p>
                                                <p><strong>Submitted:</strong> {{ $registration->registered_at->format('F j, Y g:i A') }}</p>
                                            </div>
                                            
                                            <!-- Payment Details -->
                                            <div class="bg-gray-50 rounded-lg p-4">
                                                <h5 class="font-medium text-gray-900 mb-3">Payment Details</h5>
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                                    <div>
                                                        <span class="font-medium text-gray-700">Amount:</span>
                                                        <span class="text-gray-900 ml-1">৳{{ number_format($registration->payment_amount, 2) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-gray-700">Method:</span>
                                                        <span class="text-gray-900 ml-1">{{ ucfirst(str_replace('_', ' ', $registration->payment_method)) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-gray-700">Transaction ID:</span>
                                                        <span class="text-gray-900 ml-1 font-mono">{{ $registration->transaction_id }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-gray-700">Payment Date:</span>
                                                        <span class="text-gray-900 ml-1">{{ $registration->payment_date->format('M j, Y') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex-shrink-0 flex space-x-2">
                                        <button type="button" 
                                                onclick="openApproveModal({{ $registration->id }})"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Approve
                                        </button>
                                        
                                        <button type="button" 
                                                onclick="openRejectModal({{ $registration->id }})"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Reject
                                        </button>
                                        
                                        <a href="{{ route('admin.registrations.show', $registration) }}" 
                                           class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($pendingPayments->hasPages())
                    <div class="mt-6">
                        {{ $pendingPayments->appends(request()->query())->links() }}
                    </div>
                    @endif

                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending payments</h3>
                        <p class="mt-1 text-sm text-gray-500">All payments have been processed.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Payment Modal -->
    <div id="approveModal" class="siks-modal hidden">
        <div class="siks-modal-content">
            <div class="mt-3">
                <h3 class="siks-heading-3 mb-4">Approve Payment</h3>
                <form id="approveForm" method="POST" class="space-y-4">
                    @csrf
                    <div class="siks-form-group">
                        <label class="siks-label" for="approve_notes">Admin Notes (Optional)</label>
                        <textarea id="approve_notes" name="admin_notes" rows="3" class="siks-textarea"
                                  placeholder="Add any notes about this approval..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApproveModal()" class="siks-btn-ghost">
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
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Payment</h3>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Please provide a clear reason for rejecting this payment..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                            Reject Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Approve Modal -->
    <div id="bulkApproveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Approve Payments</h3>
                <form id="bulkApproveForm" method="POST" action="{{ route('admin.registrations.bulk-approve-payments') }}">
                    @csrf
                    <input type="hidden" name="registration_ids" id="bulkRegistrationIds">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-3">
                            You are about to approve <span id="bulkCount">0</span> payment(s).
                        </p>
                        <label for="bulk_admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Notes (Optional)
                        </label>
                        <textarea id="bulk_admin_notes" name="admin_notes" rows="3"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Add notes for all selected payments..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeBulkApproveModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                            Approve All Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openApproveModal(registrationId) {
            document.getElementById('approveForm').action = `/admin/registrations/${registrationId}/approve-payment`;
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
            document.getElementById('approve_notes').value = '';
        }

        function openRejectModal(registrationId) {
            document.getElementById('rejectForm').action = `/admin/registrations/${registrationId}/reject-payment`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
        }

        function openBulkApproveModal() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;
            
            document.getElementById('bulkRegistrationIds').value = JSON.stringify(selectedIds);
            document.getElementById('bulkCount').textContent = selectedIds.length;
            document.getElementById('bulkApproveModal').classList.remove('hidden');
        }

        function closeBulkApproveModal() {
            document.getElementById('bulkApproveModal').classList.add('hidden');
            document.getElementById('bulk_admin_notes').value = '';
        }

        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.payment-checkbox:checked');
            return Array.from(checkboxes).map(cb => parseInt(cb.value));
        }

        function updateBulkActions() {
            const selectedIds = getSelectedIds();
            const bulkBtn = document.getElementById('bulkApproveBtn');
            const countSpan = document.getElementById('selectedCount');
            
            countSpan.textContent = selectedIds.length;
            bulkBtn.disabled = selectedIds.length === 0;
        }

        // Close modals when clicking outside
        ['approveModal', 'rejectModal', 'bulkApproveModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', function(e) {
                if (e.target === this) {
                    if (modalId === 'approveModal') closeApproveModal();
                    else if (modalId === 'rejectModal') closeRejectModal();
                    else if (modalId === 'bulkApproveModal') closeBulkApproveModal();
                }
            });
        });
    </script>
</x-page-layout>