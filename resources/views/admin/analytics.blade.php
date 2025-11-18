<x-page-layout>
    <x-slot name="title">Analytics - SIKS Admin</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Analytics Dashboard</h1>
            <p class="siks-body text-white/90">View detailed analytics and insights</p>
        </div>
    </x-section>

    <!-- Content -->
    <x-section>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Date Range Filter -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.analytics') }}" class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                                   class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                                   class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                            Update Analytics
                        </button>
                        
                        <div class="text-sm text-gray-600 ">
                            Showing data from {{ $startDate->format('M j, Y') }} to {{ $endDate->format('M j, Y') }}
                        </div>
                    </form>
                </div>
            </div>

            <!-- Content Creation Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 ">Blogs Created</p>
                                <p class="text-2xl font-semibold text-gray-900 ">{{ number_format($contentStats['blogs_created']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 ">Events Created</p>
                                <p class="text-2xl font-semibold text-gray-900 ">{{ number_format($contentStats['events_created']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>
                                        <path fill-rule="evenodd" d="M3 8a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 ">Fests Created</p>
                                <p class="text-2xl font-semibold text-gray-900 ">{{ number_format($contentStats['fests_created']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 ">Images Uploaded</p>
                                <p class="text-2xl font-semibold text-gray-900 ">{{ number_format($contentStats['images_uploaded']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Engagement Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">User Engagement</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 ">Active Users</span>
                                <span class="text-lg font-semibold text-gray-900 ">{{ $engagementMetrics['active_users'] }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 ">Repeat Participants</span>
                                <span class="text-lg font-semibold text-gray-900 ">{{ $engagementMetrics['repeat_participants'] }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 ">New User Registrations</span>
                                <span class="text-lg font-semibold text-gray-900 ">{{ $engagementMetrics['new_user_registrations'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Type Participation -->
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">Event Type Participation</h3>
                        <div class="space-y-3">
                            @forelse($eventParticipation as $participation)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 ">{{ $participation['event_type'] }}</span>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900 ">{{ $participation['registrations'] }} reg</div>
                                        <div class="text-xs text-gray-500 ">{{ $participation['participants'] }} people</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 ">No participation data available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Popular Events -->
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">Popular Events</h3>
                        <div class="space-y-3">
                            @forelse($popularEvents->take(5) as $event)
                                <div class="flex justify-between items-center">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900  truncate">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-500 ">{{ $event->event_date->format('M j, Y') }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                        {{ $event->registrations_count }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 ">No events with registrations.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Growth Chart -->
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">User Growth</h3>
                        @if($userGrowth->count() > 0)
                            <div class="h-64 flex items-end space-x-1">
                                @foreach($userGrowth as $growth)
                                    <div class="flex-1 flex flex-col items-center">
                                        <div class="w-full bg-blue-500 rounded-t" style="height: {{ $growth['count'] > 0 ? ($growth['count'] / $userGrowth->max('count')) * 200 : 2 }}px;"></div>
                                        <span class="text-xs text-gray-600  mt-2 transform -rotate-45 origin-top-left">{{ $growth['date'] }}</span>
                                        <span class="text-xs font-semibold text-gray-900 ">{{ $growth['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="h-64 flex items-center justify-center">
                                <p class="text-gray-500 ">No user growth data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">Daily Revenue</h3>
                        @if($revenueData->count() > 0)
                            <div class="h-64 flex items-end space-x-1">
                                @foreach($revenueData as $revenue)
                                    <div class="flex-1 flex flex-col items-center">
                                        <div class="w-full bg-green-500 rounded-t" style="height: {{ $revenue['revenue'] > 0 ? ($revenue['revenue'] / $revenueData->max('revenue')) * 200 : 2 }}px;"></div>
                                        <span class="text-xs text-gray-600  mt-2 transform -rotate-45 origin-top-left">{{ $revenue['date'] }}</span>
                                        <span class="text-xs font-semibold text-gray-900 ">৳{{ number_format($revenue['revenue']) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="h-64 flex items-center justify-center">
                                <p class="text-gray-500 ">No revenue data for this period.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- All Popular Events -->
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">All Popular Events</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Event</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Registrations</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($popularEvents as $event)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900 ">{{ $event->title }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500 ">{{ $event->event_date->format('M j, Y') }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold text-gray-900 ">{{ $event->registrations_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-center text-gray-500 ">No events with registrations.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Revenue Breakdown -->
                <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900  mb-4">Revenue Breakdown</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Revenue</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Payments</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($revenueData as $revenue)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900 ">{{ $revenue['date'] }}</td>
                                            <td class="px-4 py-2 text-sm font-semibold text-green-600">৳{{ number_format($revenue['revenue']) }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500 ">{{ $revenue['paid_registrations'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-2 text-center text-gray-500 ">No revenue data for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900  mb-4">Export Analytics Data</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('admin.export-data', ['export_type' => 'users', 'format' => 'csv']) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                            Export Users (CSV)
                        </a>
                        <a href="{{ route('admin.export-data', ['export_type' => 'events', 'format' => 'csv']) }}" 
                           class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150">
                            Export Events (CSV)
                        </a>
                        <a href="{{ route('admin.export-data', ['export_type' => 'registrations', 'format' => 'csv']) }}" 
                           class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition duration-150">
                            Export Registrations (CSV)
                        </a>
                        <a href="{{ route('admin.export-data', ['export_type' => 'all', 'format' => 'json']) }}" 
                           class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-150">
                            Export All Data (JSON)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>