<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Team') }}
        </h2>
    </x-slot>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manage Team</h1>
                    <p class="text-gray-600 mt-1">{{ $registration->team_name }} - {{ $registration->event->title }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('registrations.show', $registration) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View Registration
                    </a>
                </div>
            </div>
        </div>

        <!-- Registration Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Registration Status</h2>
                    <div class="flex items-center mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->getStatusBadgeClass() }}">
                            {{ ucfirst($registration->status) }}
                        </span>
                        @if($registration->payment_required)
                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->getPaymentStatusBadgeClass() }}">
                            Payment: {{ ucfirst($registration->payment_status) }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Registered on</p>
                    <p class="font-medium text-gray-900">{{ $registration->registered_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Team Leader -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Team Leader</h2>
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
                                Leader
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Team Members</h2>
                @if($registration->status === 'pending')
                <button type="button" 
                        onclick="toggleAddMemberForm()"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Member
                </button>
                @endif
            </div>

            <!-- Add Member Form (Hidden by default) -->
            @if($registration->status === 'pending')
            <div id="addMemberForm" class="hidden mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h3 class="text-md font-medium text-gray-900 mb-3">Add Team Member</h3>
                <form method="POST" action="{{ route('registrations.team.add-member', $registration) }}">
                    @csrf
                    <div class="flex items-end space-x-3">
                        <div class="flex-1">
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Select User
                            </label>
                            <select id="user_id" 
                                    name="user_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    required>
                                <option value="">Choose a user...</option>
                                @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->email }})
                                    @if($user->student_id) - ID: {{ $user->student_id }} @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Add
                            </button>
                            <button type="button" 
                                    onclick="toggleAddMemberForm()"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <!-- Team Members List -->
            @php
                $teamMembers = $registration->team_members_json ?? [];
            @endphp

            @if(count($teamMembers) > 0)
            <div class="space-y-3">
                @foreach($teamMembers as $index => $member)
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $member['name'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $member['email'] }}</p>
                                </div>
                                @if(isset($member['student_id']) && $member['student_id'])
                                <div>
                                    <p class="text-sm text-gray-600">ID: {{ $member['student_id'] }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
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
                        @if($registration->status === 'pending')
                        <form method="POST" action="{{ route('registrations.team.remove-member', $registration) }}" class="inline">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $member['user_id'] }}">
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to remove this member from the team?')"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                Remove
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>No team members added yet.</p>
                @if($registration->status === 'pending')
                <p class="text-sm">Click "Add Member" to invite users to your team.</p>
                @endif
            </div>
            @endif

            <!-- Team Summary -->
            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <span class="text-gray-600">Total team size:</span>
                        <span class="font-medium text-gray-900">{{ count($teamMembers) + 1 }} members</span>
                    </div>
                    @if($registration->payment_required)
                    <div>
                        <span class="text-gray-600">Total fee:</span>
                        <span class="font-medium text-gray-900">৳{{ number_format($registration->payment_amount, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        @if($registration->payment_required)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Payment Method</p>
                    <p class="font-medium text-gray-900">{{ ucfirst($registration->payment_method ?? 'Not provided') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Transaction ID</p>
                    <p class="font-medium text-gray-900">{{ $registration->transaction_id ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Payment Date</p>
                    <p class="font-medium text-gray-900">
                        {{ $registration->payment_date ? $registration->payment_date->format('F j, Y') : 'Not provided' }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Amount</p>
                    <p class="font-medium text-gray-900">৳{{ number_format($registration->payment_amount, 2) }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Admin Notes -->
        @if($registration->admin_notes)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Admin Notes</h2>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700">{{ $registration->admin_notes }}</p>
            </div>
        </div>
        @endif

        <!-- Actions -->
        @if($registration->status === 'pending')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
            <div class="flex items-center space-x-4">
                <form method="POST" action="{{ route('registrations.cancel', $registration) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            onclick="return confirm('Are you sure you want to cancel this team registration? This action cannot be undone.')"
                            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel Registration
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function toggleAddMemberForm() {
    const form = document.getElementById('addMemberForm');
    form.classList.toggle('hidden');
}
</script>
</x-app-layout>