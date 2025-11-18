<x-page-layout>
    <x-slot name="title">Activity Logs - SIKS Admin</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Activity Logs</h1>
            <p class="siks-body text-white/90">Monitor system activities and user actions</p>
        </div>
    </x-section>

    <!-- Content -->
    <x-section>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.activity-logs') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="admin_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin User</label>
                            <select name="admin_user_id" id="admin_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Admins</option>
                                @foreach($adminUsers as $admin)
                                    <option value="{{ $admin->id }}" {{ request('admin_user_id') == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="action" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Action</label>
                            <select name="action" id="action" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Admin</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white  divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 ">{{ $log->admin_name }}</div>
                                                <div class="text-sm text-gray-500 ">{{ $log->admin_email }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if(str_contains($log->action, 'approved')) bg-green-100 text-green-800
                                                @elseif(str_contains($log->action, 'rejected')) bg-red-100 text-red-800
                                                @elseif(str_contains($log->action, 'updated')) bg-blue-100 text-blue-800
                                                @elseif(str_contains($log->action, 'deleted')) bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log->details)
                                                @php
                                                    $details = json_decode($log->details, true);
                                                @endphp
                                                @if(is_array($details))
                                                    <div class="text-sm text-gray-900  space-y-1">
                                                        @foreach($details as $key => $value)
                                                            @if(!is_array($value) && !is_object($value))
                                                                <div><span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-900 ">{{ $log->details }}</div>
                                                @endif
                                            @else
                                                <span class="text-sm text-gray-400 dark:text-gray-500">No details</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 ">
                                            {{ $log->ip_address ?? 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 ">{{ \Carbon\Carbon::parse($log->created_at)->format('M j, Y') }}</div>
                                            <div class="text-sm text-gray-500 ">{{ \Carbon\Carbon::parse($log->created_at)->format('g:i A') }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 ">
                                            No activity logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="mt-6 bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900  mb-4">Activity Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ $logs->total() }}</p>
                            <p class="text-sm text-gray-600 ">Total Activities</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ $adminUsers->count() }}</p>
                            <p class="text-sm text-gray-600 ">Active Admins</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-purple-600">{{ $actions->count() }}</p>
                            <p class="text-sm text-gray-600 ">Action Types</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>