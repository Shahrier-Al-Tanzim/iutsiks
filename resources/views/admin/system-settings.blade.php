<x-page-layout>
    <x-slot name="title">System Settings - SIKS Admin</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">System Settings</h1>
            <p class="siks-body text-white/90">Configure system-wide settings and preferences</p>
        </div>
    </x-section>

    <!-- Content -->
    <x-section>
    

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- System Configuration -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900  mb-6">System Configuration</h3>
                    
                    <form method="POST" action="{{ route('admin.system-settings.update') }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Site Information -->
                            <div class="space-y-4">
                                <h4 class="text-md font-medium text-gray-900 ">Site Information</h4>
                                
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site Name</label>
                                    <input type="text" name="site_name" id="site_name" value="{{ $settings['site_name'] }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                
                                <div>
                                    <label for="site_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site Description</label>
                                    <textarea name="site_description" id="site_description" rows="3" required
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ $settings['site_description'] }}</textarea>
                                </div>
                                
                                <div>
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Email</label>
                                    <input type="email" name="contact_email" id="contact_email" value="{{ $settings['contact_email'] }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                            </div>
                            
                            <!-- Event Settings -->
                            <div class="space-y-4">
                                <h4 class="text-md font-medium text-gray-900 ">Event Settings</h4>
                                
                                <div>
                                    <label for="max_registration_per_event" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Registration Per Event</label>
                                    <input type="number" name="max_registration_per_event" id="max_registration_per_event" 
                                           value="{{ $settings['max_registration_per_event'] }}" min="1" max="1000" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                
                                <div>
                                    <label for="default_event_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Event Fee (৳)</label>
                                    <input type="number" name="default_event_fee" id="default_event_fee" 
                                           value="{{ $settings['default_event_fee'] }}" min="0" step="0.01" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                                
                                <div>
                                    <label for="registration_deadline_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Deadline (Days Before Event)</label>
                                    <input type="number" name="registration_deadline_days" id="registration_deadline_days" 
                                           value="{{ $settings['registration_deadline_days'] }}" min="1" max="30" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                </div>
                            </div>
                        </div>
                        
                        <!-- File Upload Settings -->
                        <div class="mt-6">
                            <h4 class="text-md font-medium text-gray-900  mb-4">File Upload Settings</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="gallery_max_file_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gallery Max File Size (KB)</label>
                                    <input type="number" name="gallery_max_file_size" id="gallery_max_file_size" 
                                           value="{{ $settings['gallery_max_file_size'] }}" min="512" max="10240" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <p class="mt-1 text-sm text-gray-500 ">Current: {{ number_format($settings['gallery_max_file_size']) }} KB ({{ number_format($settings['gallery_max_file_size'] / 1024, 1) }} MB)</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="mt-6">
                            <h4 class="text-md font-medium text-gray-900  mb-4">Payment Methods</h4>
                            <div class="space-y-2">
                                @foreach($settings['payment_methods'] as $method)
                                    <div class="flex items-center">
                                        <input type="checkbox" name="payment_methods[]" value="{{ $method }}" checked
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $method)) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                                Update Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900  mb-6">System Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 ">Laravel Version</span>
                                <span class="text-sm font-medium text-gray-900 ">{{ app()->version() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 ">PHP Version</span>
                                <span class="text-sm font-medium text-gray-900 ">{{ PHP_VERSION }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 ">Environment</span>
                                <span class="text-sm font-medium text-gray-900 ">{{ app()->environment() }}</span>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 ">Database</span>
                                <span class="text-sm font-medium text-gray-900 ">{{ config('database.default') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 ">Cache Driver</span>
                                <span class="text-sm font-medium text-gray-900 ">{{ config('cache.default') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 ">Queue Driver</span>
                                <span class="text-sm font-medium text-gray-900 ">{{ config('queue.default') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Export -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900  mb-6">Data Export</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-md font-medium text-gray-900  mb-4">Export System Data</h4>
                            <div class="space-y-3">
                                <a href="{{ route('admin.export-data', ['export_type' => 'users', 'format' => 'csv']) }}" 
                                   class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                                    Export Users (CSV)
                                </a>
                                <a href="{{ route('admin.export-data', ['export_type' => 'events', 'format' => 'csv']) }}" 
                                   class="block w-full text-center bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150">
                                    Export Events (CSV)
                                </a>
                                <a href="{{ route('admin.export-data', ['export_type' => 'registrations', 'format' => 'csv']) }}" 
                                   class="block w-full text-center bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition duration-150">
                                    Export Registrations (CSV)
                                </a>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-md font-medium text-gray-900  mb-4">Complete Backup</h4>
                            <div class="space-y-3">
                                <a href="{{ route('admin.export-data', ['export_type' => 'all', 'format' => 'json']) }}" 
                                   class="block w-full text-center bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-150">
                                    Export All Data (JSON)
                                </a>
                                <p class="text-sm text-gray-600 ">
                                    This will export all users, events, registrations, fests, and system data in JSON format.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Maintenance -->
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900  mb-6">System Maintenance</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 mb-2">{{ \App\Models\User::count() }}</div>
                            <div class="text-sm text-gray-600 ">Total Users</div>
                        </div>
                        
                        <div class="text-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-green-600 mb-2">{{ \App\Models\Event::count() }}</div>
                            <div class="text-sm text-gray-600 ">Total Events</div>
                        </div>
                        
                        <div class="text-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600 mb-2">{{ \App\Models\Registration::count() }}</div>
                            <div class="text-sm text-gray-600 ">Total Registrations</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">System Maintenance Notice</h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>Regular system maintenance should be performed to ensure optimal performance. This includes clearing cache, optimizing database, and reviewing logs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-layout>