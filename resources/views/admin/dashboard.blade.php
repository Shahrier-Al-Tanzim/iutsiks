<x-page-layout>
    <x-slot name="title">Admin Dashboard - SIKS</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Admin Dashboard</h1>
            <p class="siks-body text-white/90">Manage SIKS system and monitor activities</p>
        </div>
    </x-section>

    <!-- Dashboard Content -->
    <x-section>
        <div class="space-y-8">
            
            <!-- System Overview Cards -->
            <div class="siks-grid-4 mb-8">
                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-siks-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ number_format($stats['total_users']) }}</h3>
                    <p class="siks-body-small text-gray-600 mb-2">Total Users</p>
                    <span class="text-sm text-siks-primary">+{{ $recentStats['new_users_this_month'] }} this month</span>
                </div>

                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ number_format($stats['total_events']) }}</h3>
                    <p class="siks-body-small text-gray-600 mb-2">Total Events</p>
                    <span class="text-sm text-green-600">+{{ $recentStats['new_events_this_month'] }} this month</span>
                </div>

                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h4a2 2 0 002-2V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ number_format($stats['total_registrations']) }}</h3>
                    <p class="siks-body-small text-gray-600 mb-2">Total Registrations</p>
                    <span class="text-sm text-purple-600">+{{ $recentStats['new_registrations_this_month'] }} this month</span>
                </div>

                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">৳{{ number_format($revenueStats['total_revenue']) }}</h3>
                    <p class="siks-body-small text-gray-600 mb-2">Total Revenue</p>
                    <span class="text-sm text-yellow-600">৳{{ number_format($revenueStats['this_month_revenue']) }} this month</span>
                </div>
            </div>

            <!-- Registration Status Overview -->
            <div class="siks-grid-2 gap-6">
                <div class="siks-card p-6">
                    <h3 class="siks-heading-4 mb-4">Registration Status</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="siks-body-small">Pending Registrations</span>
                            <span class="px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                {{ $registrationStats['pending_registrations'] }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="siks-body-small">Approved Registrations</span>
                            <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                {{ $registrationStats['approved_registrations'] }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="siks-body-small">Rejected Registrations</span>
                            <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                {{ $registrationStats['rejected_registrations'] }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="siks-body-small">Pending Payments</span>
                            <span class="px-3 py-1 text-xs font-semibold bg-orange-100 text-orange-800 rounded-full">
                                {{ $registrationStats['pending_payments'] }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.registrations.index') }}" class="text-sm text-siks-primary hover:text-siks-dark">
                            View all registrations →
                        </a>
                    </div>
                </div>

                <div class="siks-card p-6">
                    <h3 class="siks-heading-4 mb-4">User Role Distribution</h3>
                    <div class="space-y-3">
                        @foreach($userRoleDistribution as $role => $count)
                            <div class="flex justify-between items-center">
                                <span class="siks-body-small">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                <span class="px-3 py-1 text-xs font-semibold bg-siks-primary/10 text-siks-primary rounded-full">
                                    {{ $count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.user-management') }}" class="text-sm text-siks-primary hover:text-siks-dark">
                            Manage users →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics -->
            <div class="siks-grid-2 gap-6">
                <!-- Monthly Registration Trends -->
                <div class="siks-card p-6">
                    <h3 class="siks-heading-4 mb-4">Registration Trends (Last 6 Months)</h3>
                    <div class="h-64 flex items-end space-x-2">
                        @foreach($monthlyTrends as $trend)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-siks-primary rounded-t" style="height: {{ $trend['count'] > 0 ? ($trend['count'] / $monthlyTrends->max('count')) * 200 : 2 }}px;"></div>
                                <span class="text-xs text-gray-600 mt-2">{{ $trend['month'] }}</span>
                                <span class="text-xs font-semibold text-gray-900">{{ $trend['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Popular Events -->
                <div class="siks-card p-6">
                    <h3 class="siks-heading-4 mb-4">Popular Events</h3>
                    <div class="space-y-3">
                        @forelse($popularEvents as $event)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                                    @if($event->fest)
                                        <p class="text-xs text-gray-500">{{ $event->fest->title }}</p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold bg-siks-primary/10 text-siks-primary rounded-full">
                                    {{ $event->registrations_count }} registrations
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No events with registrations yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="siks-grid-2 gap-6">
                <!-- Recent Registrations -->
                <div class="siks-card p-6">
                    <h3 class="siks-heading-4 mb-4">Recent Registrations (Last 7 Days)</h3>
                    <div class="space-y-3">
                        @forelse($recentRegistrations as $registration)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $registration->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $registration->event->title }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $registration->getStatusBadgeClass() }}">
                                        {{ ucfirst($registration->status) }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $registration->registered_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recent registrations.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="siks-card p-6">
                    <h3 class="siks-heading-4 mb-4">Upcoming Events</h3>
                    <div class="space-y-3">
                        @forelse($upcomingEvents as $event)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                                    @if($event->fest)
                                        <p class="text-xs text-gray-500">{{ $event->fest->title }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-semibold text-gray-900">{{ $event->event_date->format('M j, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $event->event_time ? $event->event_time->format('g:i A') : 'Time TBD' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No upcoming events.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- System Health Indicators -->
            <div class="siks-card p-6">
                <h3 class="siks-heading-4 mb-4">System Health</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $systemHealth['active_fests'] }}</p>
                        <p class="siks-body-small">Active Fests</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-siks-primary">{{ $systemHealth['published_events'] }}</p>
                        <p class="siks-body-small">Published Events</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $systemHealth['recent_prayer_updates'] }}</p>
                        <p class="siks-body-small">Prayer Updates (7d)</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $systemHealth['recent_gallery_uploads'] }}</p>
                        <p class="siks-body-small">Gallery Uploads (7d)</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="siks-card p-6">
                <h3 class="siks-heading-4 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('admin.registrations.index') }}" class="siks-btn-primary text-center">
                        Manage Registrations
                    </a>
                    <a href="{{ route('admin.user-management') }}" class="siks-btn-ghost text-center">
                        User Management
                    </a>
                    <a href="{{ route('admin.prayer-times.index') }}" class="siks-btn-ghost text-center">
                        Prayer Times
                    </a>
                    <a href="{{ route('admin.analytics') }}" class="siks-btn-ghost text-center">
                        View Analytics
                    </a>
                </div>
            </div>
        </div>
    </x-section>
</x-page-layout>