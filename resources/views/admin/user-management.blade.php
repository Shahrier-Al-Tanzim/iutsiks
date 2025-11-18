<x-page-layout>
    <x-slot name="title">User Management - SIKS Admin</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">User Management</h1>
            <p class="siks-body text-white/90">Manage user roles and permissions</p>
        </div>
    </x-section>

    <!-- Content -->
    <x-section>
        <div class="space-y-6">
            
            <!-- Role Statistics -->
            <div class="siks-grid-4 mb-8">
                @foreach($roleStats as $role => $count)
                    <div class="siks-card p-6 text-center">
                        <h3 class="siks-heading-4 mb-2">{{ $count }}</h3>
                        <p class="siks-body-small text-gray-600">{{ ucfirst(str_replace('_', ' ', $role)) }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Filters -->
            <div class="siks-card p-6 mb-6">
                <form method="GET" action="{{ route('admin.user-management') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="siks-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Name, email, or student ID"
                               class="siks-input">
                    </div>
                    
                    <div>
                        <label for="role" class="siks-label">Role</label>
                        <select name="role" id="role" class="siks-select">
                            <option value="">All Roles</option>
                            <option value="member" {{ request('role') === 'member' ? 'selected' : '' }}>Member</option>
                            <option value="event_admin" {{ request('role') === 'event_admin' ? 'selected' : '' }}>Event Admin</option>
                            <option value="content_admin" {{ request('role') === 'content_admin' ? 'selected' : '' }}>Content Admin</option>
                            <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="has_registrations" class="siks-label">Registration Status</label>
                        <select name="has_registrations" id="has_registrations" class="siks-select">
                            <option value="">All Users</option>
                            <option value="yes" {{ request('has_registrations') === 'yes' ? 'selected' : '' }}>Has Registrations</option>
                            <option value="no" {{ request('has_registrations') === 'no' ? 'selected' : '' }}>No Registrations</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="siks-btn-primary w-full">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="siks-card p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            @if($user->student_id)
                                                <div class="text-xs text-gray-400">ID: {{ $user->student_id }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            @if($user->role === 'super_admin') bg-red-100 text-red-800
                                            @elseif($user->role === 'content_admin') bg-purple-100 text-purple-800
                                            @elseif($user->role === 'event_admin') bg-siks-primary/10 text-siks-primary
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($user->phone)
                                            <div>{{ $user->phone }}</div>
                                        @else
                                            <span class="text-gray-400">No phone</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="space-y-1">
                                            <div class="flex items-center">
                                                <span class="w-2 h-2 bg-siks-primary rounded-full mr-2"></span>
                                                {{ $user->registrations_count }} registrations
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span>
                                                {{ $user->authored_blogs_count }} blogs
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                                {{ $user->authored_events_count }} events
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-3">
                                            <!-- Role Change Modal Trigger -->
                                            <button onclick="openRoleModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->role }}')" 
                                                    class="text-siks-primary hover:text-siks-dark transition-colors">
                                                Change Role
                                            </button>
                                            
                                            <!-- Password Reset Modal Trigger -->
                                            <button onclick="openPasswordModal({{ $user->id }}, '{{ $user->name }}')" 
                                                    class="text-orange-600 hover:text-orange-700 transition-colors">
                                                Reset Password
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                            </svg>
                                            <p class="text-gray-500 text-sm">No users found matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-section>

    <!-- Role Change Modal -->
    <div id="roleModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-6 border-0 w-96 shadow-xl rounded-lg bg-white">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="siks-heading-4">Change User Role</h3>
                    <button onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="roleForm" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-4">
                        <div class="bg-gray-50 p-3 rounded-lg mb-4">
                            <p class="siks-body-small text-gray-600">User: <span id="roleUserName" class="font-medium text-gray-900"></span></p>
                        </div>
                        
                        <label for="role_select" class="siks-label">New Role</label>
                        <select name="role" id="role_select" required class="siks-select">
                            <option value="member">Member</option>
                            <option value="event_admin">Event Admin</option>
                            <option value="content_admin">Content Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label for="role_reason" class="siks-label">Reason (Optional)</label>
                        <textarea name="reason" id="role_reason" rows="3" 
                                  class="siks-textarea"
                                  placeholder="Reason for role change..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRoleModal()" 
                                class="siks-btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="siks-btn-primary">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-6 border-0 w-96 shadow-xl rounded-lg bg-white">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="siks-heading-4">Reset User Password</h3>
                    <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form id="passwordForm" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-4">
                        <div class="bg-gray-50 p-3 rounded-lg mb-4">
                            <p class="siks-body-small text-gray-600">User: <span id="passwordUserName" class="font-medium text-gray-900"></span></p>
                        </div>
                        
                        <label for="new_password" class="siks-label">New Password</label>
                        <input type="password" name="new_password" id="new_password" required minlength="8"
                               class="siks-input">
                    </div>
                    
                    <div class="mb-4">
                        <label for="new_password_confirmation" class="siks-label">Confirm Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required minlength="8"
                               class="siks-input">
                    </div>
                    
                    <div class="mb-6">
                        <label for="password_reason" class="siks-label">Reason (Optional)</label>
                        <textarea name="reason" id="password_reason" rows="3" 
                                  class="siks-textarea"
                                  placeholder="Reason for password reset..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closePasswordModal()" 
                                class="siks-btn-ghost">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRoleModal(userId, userName, currentRole) {
            document.getElementById('roleUserName').textContent = userName;
            document.getElementById('role_select').value = currentRole;
            document.getElementById('roleForm').action = `/admin/users/${userId}/role`;
            document.getElementById('roleModal').classList.remove('hidden');
        }

        function closeRoleModal() {
            document.getElementById('roleModal').classList.add('hidden');
            document.getElementById('roleForm').reset();
        }

        function openPasswordModal(userId, userName) {
            document.getElementById('passwordUserName').textContent = userName;
            document.getElementById('passwordForm').action = `/admin/users/${userId}/password`;
            document.getElementById('passwordModal').classList.remove('hidden');
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.getElementById('passwordForm').reset();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const roleModal = document.getElementById('roleModal');
            const passwordModal = document.getElementById('passwordModal');
            
            if (event.target === roleModal) {
                closeRoleModal();
            }
            if (event.target === passwordModal) {
                closePasswordModal();
            }
        }
    </script>
</x-page-layout>