<x-page-layout>
    <x-slot name="title">Dashboard - SIKS</x-slot>
    
    <!-- Welcome Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">
                Welcome back, {{ $user->name }}!
            </h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                Stay connected with SIKS activities and manage your event registrations.
            </p>
        </div>
    </x-section>

    <!-- Dashboard Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
            <!-- Quick Stats -->
            <div class="siks-grid-4 mb-12">
                <!-- Total Registrations -->
                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ $user->registrations()->count() }}</h3>
                    <p class="siks-body-small text-gray-600">Total Registrations</p>
                </div>

                <!-- Upcoming Events -->
                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ $upcomingEvents->count() }}</h3>
                    <p class="siks-body-small text-gray-600">Upcoming Events</p>
                </div>

                <!-- Pending Registrations -->
                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ $user->registrations()->where('status', 'pending')->count() }}</h3>
                    <p class="siks-body-small text-gray-600">Pending Approvals</p>
                </div>

                <!-- Profile Completion -->
                <div class="siks-card p-6 text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-4 mb-2">{{ $user->name ? '100%' : '80%' }}</h3>
                    <p class="siks-body-small text-gray-600">Profile Complete</p>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Recent Registrations -->
                    <div class="siks-card p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="siks-heading-3">Recent Registrations</h2>
                            <a href="{{ route('registrations.history') }}" class="siks-btn-ghost text-sm">
                                View All
                            </a>
                        </div>
                        
                        @if($userRegistrations->count() > 0)
                            <div class="space-y-4">
                                @foreach($userRegistrations as $registration)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="siks-heading-4 mb-1">{{ $registration->event->title }}</h4>
                                                <p class="siks-body-small text-gray-600">
                                                    {{ $registration->event->event_date->format('M j, Y') }} • 
                                                    <span class="capitalize">{{ $registration->status }}</span>
                                                </p>
                                            </div>
                                            <a href="{{ route('registrations.show', $registration) }}" class="siks-btn-ghost text-sm">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="siks-body text-gray-600 mb-4">No registrations yet</p>
                                <a href="{{ route('events.index') }}" class="siks-btn-primary">
                                    Browse Events
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Upcoming Events -->
                    @if($upcomingEvents->count() > 0)
                        <div class="siks-card p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="siks-heading-3">Upcoming Events</h2>
                                <a href="{{ route('events.index') }}" class="siks-btn-ghost text-sm">
                                    View All
                                </a>
                            </div>
                            
                            <div class="space-y-4">
                                @foreach($upcomingEvents as $event)
                                    <x-event-card :event="$event" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Prayer Times Widget -->
                    @if($todaysPrayerTimes)
                        <div class="siks-card p-6">
                            <h3 class="siks-heading-4 mb-4">Today's Prayer Times</h3>
                            <x-prayer-times-widget :prayerTimes="$todaysPrayerTimes" />
                            <div class="mt-4">
                                <a href="{{ route('prayer-times.index') }}" class="siks-btn-ghost text-sm w-full">
                                    View Full Schedule
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="siks-card p-6">
                        <h3 class="siks-heading-4 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('events.index') }}" class="siks-btn-outline w-full">
                                Browse Events
                            </a>
                            <a href="{{ route('registrations.history') }}" class="siks-btn-outline w-full">
                                My Registrations
                            </a>
                            <a href="{{ route('blogs.index') }}" class="siks-btn-outline w-full">
                                Read Blogs
                            </a>
                            <a href="{{ route('gallery.index') }}" class="siks-btn-outline w-full">
                                View Gallery
                            </a>
                        </div>
                    </div>

                    <!-- Admin Quick Access -->
                    @if($user->isSuperAdmin() || $user->hasRole(['event_admin', 'prayer_admin']))
                        <div class="siks-card p-6 border-l-4 border-siks-primary">
                            <h3 class="siks-heading-4 mb-4">Admin Panel</h3>
                            <div class="space-y-3">
                                @if($user->isSuperAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="siks-btn-primary w-full text-sm">
                                        Admin Dashboard
                                    </a>
                                    <a href="{{ route('admin.user-management') }}" class="siks-btn-ghost w-full text-sm">
                                        User Management
                                    </a>
                                @endif
                                
                                @can('manage-events')
                                    <a href="{{ route('admin.registrations.index') }}" class="siks-btn-ghost w-full text-sm">
                                        Manage Registrations
                                    </a>
                                @endcan
                                
                                @can('manage-prayer-times')
                                    <a href="{{ route('admin.prayer-times.index') }}" class="siks-btn-ghost w-full text-sm">
                                        Manage Prayer Times
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-section>
</x-page-layout>
