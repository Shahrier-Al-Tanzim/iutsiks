<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Registration') }}
        </h2>
    </x-slot>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Team Registration</h1>
                    <p class="text-gray-600 mt-1">Register your team for {{ $event->title }}</p>
                </div>
                <a href="{{ route('events.show', $event) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Event
                </a>
            </div>
        </div>

        <!-- Event Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Event</p>
                    <p class="font-medium text-gray-900">{{ $event->title }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Date & Time</p>
                    <p class="font-medium text-gray-900">
                        {{ $event->event_date->format('F j, Y') }}
                        @if($event->event_time)
                            at {{ $event->event_time->format('g:i A') }}
                        @endif
                    </p>
                </div>
                @if($event->location)
                <div>
                    <p class="text-sm text-gray-600">Location</p>
                    <p class="font-medium text-gray-900">{{ $event->location }}</p>
                </div>
                @endif
                @if($event->fee_amount > 0)
                <div>
                    <p class="text-sm text-gray-600">Registration Fee (per member)</p>
                    <p class="font-medium text-gray-900">৳{{ number_format($event->fee_amount, 2) }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Availability Information -->
        @if($availability['max_participants'])
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-blue-800 font-medium">
                        {{ $availability['remaining_spots'] }} spots remaining out of {{ $availability['max_participants'] }}
                    </p>
                    <p class="text-blue-600 text-sm">
                        Currently {{ $availability['total_registered'] }} participants registered
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Registration Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Team Registration Form</h2>

            <form method="POST" action="{{ route('registrations.team.store', $event) }}" id="teamRegistrationForm">
                @csrf

                <!-- Team Name -->
                <div class="mb-6">
                    <label for="team_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Team Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="team_name" 
                           name="team_name" 
                           value="{{ old('team_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('team_name') border-red-500 @enderror"
                           placeholder="Enter your team name"
                           required>
                    @error('team_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Team Leader Information -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Team Leader (You)</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Name</p>
                                <p class="font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium text-gray-900">{{ Auth::user()->email }}</p>
                            </div>
                            @if(Auth::user()->student_id)
                            <div>
                                <p class="text-sm text-gray-600">Student ID</p>
                                <p class="font-medium text-gray-900">{{ Auth::user()->student_id }}</p>
                            </div>
                            @endif
                            @if(Auth::user()->phone)
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="font-medium text-gray-900">{{ Auth::user()->phone }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Team Members Selection -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">
                        Team Members <span class="text-red-500">*</span>
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Select team members from registered users. They will receive invitation emails to join your team.
                    </p>

                    <div class="space-y-3" id="teamMembersContainer">
                        @if($availableUsers->count() > 0)
                            @foreach($availableUsers as $user)
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <input type="checkbox" 
                                       id="member_{{ $user->id }}" 
                                       name="team_members[]" 
                                       value="{{ $user->id }}"
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                       {{ in_array($user->id, old('team_members', [])) ? 'checked' : '' }}>
                                <label for="member_{{ $user->id }}" class="ml-3 flex-1 cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                        </div>
                                        @if($user->student_id)
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">ID: {{ $user->student_id }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p>No available users found for team selection.</p>
                                <p class="text-sm">All registered users may already be registered for this event.</p>
                            </div>
                        @endif
                    </div>

                    @error('team_members')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('team_members.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-3 text-sm text-gray-600">
                        <p>Selected members: <span id="selectedCount">0</span></p>
                    </div>
                </div>

                <!-- Payment Information (if required) -->
                @if($event->fee_amount > 0)
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Payment Information</h3>
                    <p class="text-sm text-gray-700 mb-4">
                        This event requires a registration fee of ৳{{ number_format($event->fee_amount, 2) }} per team member.
                        Total fee will be calculated based on your team size (including team leader).
                    </p>
                    <p class="text-sm text-gray-600 mb-4">
                        <strong>Current team size:</strong> <span id="teamSizeDisplay">1</span> member(s)<br>
                        <strong>Total fee:</strong> ৳<span id="totalFeeDisplay">{{ number_format($event->fee_amount, 2) }}</span>
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select id="payment_method" 
                                    name="payment_method" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('payment_method') border-red-500 @enderror"
                                    required>
                                <option value="">Select method</option>
                                <option value="bkash" {{ old('payment_method') === 'bkash' ? 'selected' : '' }}>bKash</option>
                                <option value="nagad" {{ old('payment_method') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Transaction ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="transaction_id" 
                                   name="transaction_id" 
                                   value="{{ old('transaction_id') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('transaction_id') border-red-500 @enderror"
                                   placeholder="Enter transaction ID"
                                   required>
                            @error('transaction_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Payment Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="payment_date" 
                                   name="payment_date" 
                                   value="{{ old('payment_date', date('Y-m-d')) }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('payment_date') border-red-500 @enderror"
                                   required>
                            @error('payment_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="terms" 
                               name="terms" 
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1"
                               required>
                        <label for="terms" class="ml-3 text-sm text-gray-700">
                            I agree to the terms and conditions and confirm that all team members have consented to participate in this event. 
                            I understand that team invitations will be sent to selected members and they must accept to complete the registration.
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('events.show', $event) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="submitButton">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Register Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="team_members[]"]');
    const selectedCountSpan = document.getElementById('selectedCount');
    const teamSizeDisplay = document.getElementById('teamSizeDisplay');
    const totalFeeDisplay = document.getElementById('totalFeeDisplay');
    const feePerMember = {{ $event->fee_amount }};

    function updateCounts() {
        const selectedCount = document.querySelectorAll('input[name="team_members[]"]:checked').length;
        const teamSize = selectedCount + 1; // +1 for team leader
        
        selectedCountSpan.textContent = selectedCount;
        
        if (teamSizeDisplay) {
            teamSizeDisplay.textContent = teamSize;
        }
        
        if (totalFeeDisplay) {
            const totalFee = teamSize * feePerMember;
            totalFeeDisplay.textContent = totalFee.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCounts);
    });

    // Initial count update
    updateCounts();

    // Form validation
    const form = document.getElementById('teamRegistrationForm');
    form.addEventListener('submit', function(e) {
        const selectedMembers = document.querySelectorAll('input[name="team_members[]"]:checked').length;
        
        if (selectedMembers === 0) {
            e.preventDefault();
            alert('Please select at least one team member.');
            return false;
        }
    });
});
</script>
</x-app-layout>