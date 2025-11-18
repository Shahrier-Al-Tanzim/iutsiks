<x-page-layout>
    <x-slot name="title">Prayer Times Management - SIKS Admin</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Prayer Times Management</h1>
            <p class="siks-body text-white/90">Manage daily prayer times for IOT Masjid</p>
        </div>
    </x-section>

    <!-- Content -->
    <x-section>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Today's Prayer Times Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Today's Prayer Times Status</h3>
                    
                    @if($todaysPrayerTimes)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-green-800 font-medium">
                                        ✓ Prayer times are set for {{ now()->format('F j, Y') }}
                                    </p>
                                    <p class="text-green-600 text-sm mt-1">
                                        Last updated by {{ $todaysPrayerTimes->updatedBy->name }} 
                                        on {{ $todaysPrayerTimes->updated_at->format('F j, Y \a\t g:i A') }}
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('prayer-times.index') }}" 
                                       class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        View Public
                                    </a>
                                    <a href="{{ route('admin.prayer-times.edit') }}" 
                                       class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-yellow-800 font-medium">
                                        ⚠ No prayer times set for today ({{ now()->format('F j, Y') }})
                                    </p>
                                    <p class="text-yellow-600 text-sm mt-1">
                                        Users will see a message that prayer times are not available.
                                    </p>
                                </div>
                                <a href="{{ route('admin.prayer-times.edit') }}" 
                                   class="inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Set Now
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Prayer Times -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Recent Prayer Times</h3>
                    
                    @if($recentPrayerTimes->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fajr
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dhuhr
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Asr
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Maghrib
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Isha
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Updated By
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentPrayerTimes as $prayerTime)
                                        <tr class="{{ $prayerTime->date->isToday() ? 'bg-green-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $prayerTime->date->format('M j, Y') }}
                                                @if($prayerTime->date->isToday())
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Today
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($prayerTime->fajr)->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($prayerTime->dhuhr)->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($prayerTime->asr)->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($prayerTime->maghrib)->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($prayerTime->isha)->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $prayerTime->updatedBy->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('prayer-times.show', $prayerTime->date->format('Y-m-d')) }}" 
                                                       class="text-blue-600 hover:text-blue-900">View</a>
                                                    <a href="{{ route('admin.prayer-times.edit', $prayerTime->date->format('Y-m-d')) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                    @if(!$prayerTime->date->isToday())
                                                        <form method="POST" action="{{ route('admin.prayer-times.destroy', $prayerTime->date->format('Y-m-d')) }}" 
                                                              class="inline" onsubmit="return confirm('Are you sure you want to delete prayer times for {{ $prayerTime->date->format('F j, Y') }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Prayer Times Set</h3>
                            <p class="text-gray-600 mb-4">Start by setting prayer times for today or use bulk update for multiple days.</p>
                            <div class="flex justify-center space-x-4">
                                <a href="{{ route('admin.prayer-times.edit') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Set Today's Times
                                </a>
                                <a href="{{ route('admin.prayer-times.bulk-edit') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Bulk Update
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    </x-section>
</x-page-layout>