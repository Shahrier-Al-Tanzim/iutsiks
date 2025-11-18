<x-page-layout>
    <x-slot name="title">Admin - SIKS</x-slot>
    

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Please correct the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Instructions -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">Bulk Update Instructions:</h3>
                        <ul class="text-blue-700 text-sm space-y-1">
                            <li>• Set prayer times for multiple days at once</li>
                            <li>• Times must be in chronological order for each day</li>
                            <li>• Existing prayer times will be updated, new ones will be created</li>
                            <li>• Use the "Copy from Previous" button to duplicate times from the day above</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('admin.prayer-times.bulk-update') }}" id="bulk-form">
                        @csrf
                        @method('PUT')

                        <!-- Date Range Info -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                Setting prayer times for {{ $startDate->format('F j') }} - {{ $endDate->format('F j, Y') }}
                            </h3>
                        </div>

                        <!-- Prayer Times Table -->
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
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $currentDate = $startDate->copy()->addDays($i);
                                            $existing = $existingTimes->firstWhere('date', $currentDate->format('Y-m-d'));
                                        @endphp
                                        <tr class="{{ $currentDate->isToday() ? 'bg-green-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $currentDate->format('M j, Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $currentDate->format('l') }}
                                                    @if($currentDate->isToday())
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Today
                                                        </span>
                                                    @endif
                                                </div>
                                                <input type="hidden" name="prayer_times[{{ $i }}][date]" value="{{ $currentDate->format('Y-m-d') }}">
                                            </td>
                                            
                                            <!-- Fajr -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="time" 
                                                       name="prayer_times[{{ $i }}][fajr]" 
                                                       value="{{ old("prayer_times.{$i}.fajr", $existing ? substr($existing->fajr, 0, 5) : '05:30') }}"
                                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                       required>
                                            </td>
                                            
                                            <!-- Dhuhr -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="time" 
                                                       name="prayer_times[{{ $i }}][dhuhr]" 
                                                       value="{{ old("prayer_times.{$i}.dhuhr", $existing ? substr($existing->dhuhr, 0, 5) : '12:15') }}"
                                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                       required>
                                            </td>
                                            
                                            <!-- Asr -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="time" 
                                                       name="prayer_times[{{ $i }}][asr]" 
                                                       value="{{ old("prayer_times.{$i}.asr", $existing ? substr($existing->asr, 0, 5) : '15:45') }}"
                                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                       required>
                                            </td>
                                            
                                            <!-- Maghrib -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="time" 
                                                       name="prayer_times[{{ $i }}][maghrib]" 
                                                       value="{{ old("prayer_times.{$i}.maghrib", $existing ? substr($existing->maghrib, 0, 5) : '18:30') }}"
                                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                       required>
                                            </td>
                                            
                                            <!-- Isha -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="time" 
                                                       name="prayer_times[{{ $i }}][isha]" 
                                                       value="{{ old("prayer_times.{$i}.isha", $existing ? substr($existing->isha, 0, 5) : '19:45') }}"
                                                       class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                       required>
                                            </td>
                                            
                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($i > 0)
                                                    <button type="button" 
                                                            onclick="copyFromPrevious({{ $i }})"
                                                            class="text-blue-600 hover:text-blue-900 text-xs">
                                                        Copy from Previous
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        <!-- Hidden fields for location and notes -->
                                        <input type="hidden" name="prayer_times[{{ $i }}][location]" value="{{ $existing ? $existing->location : 'IOT Masjid' }}">
                                        <input type="hidden" name="prayer_times[{{ $i }}][notes]" value="{{ $existing ? $existing->notes : '' }}">
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- Global Actions -->
                        <div class="mt-6 bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Quick Actions:</h4>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" 
                                        onclick="setAllTimes('05:30', '12:15', '15:45', '18:30', '19:45')"
                                        class="px-3 py-2 bg-blue-100 text-blue-800 text-sm rounded-md hover:bg-blue-200">
                                    Set Default Times
                                </button>
                                <button type="button" 
                                        onclick="adjustAllTimes(5)"
                                        class="px-3 py-2 bg-green-100 text-green-800 text-sm rounded-md hover:bg-green-200">
                                    +5 minutes all
                                </button>
                                <button type="button" 
                                        onclick="adjustAllTimes(-5)"
                                        class="px-3 py-2 bg-red-100 text-red-800 text-sm rounded-md hover:bg-red-200">
                                    -5 minutes all
                                </button>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-between mt-6">
                            <x-primary-button>
                                {{ __('Update All Prayer Times') }}
                            </x-primary-button>
                            
                            <a href="{{ route('admin.prayer-times.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for bulk operations -->
    <script>
        function copyFromPrevious(currentIndex) {
            const previousIndex = currentIndex - 1;
            const prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
            
            prayers.forEach(prayer => {
                const previousValue = document.querySelector(`input[name="prayer_times[${previousIndex}][${prayer}]"]`).value;
                document.querySelector(`input[name="prayer_times[${currentIndex}][${prayer}]"]`).value = previousValue;
            });
        }
        
        function setAllTimes(fajr, dhuhr, asr, maghrib, isha) {
            const times = { fajr, dhuhr, asr, maghrib, isha };
            
            for (let i = 0; i < 7; i++) {
                Object.keys(times).forEach(prayer => {
                    document.querySelector(`input[name="prayer_times[${i}][${prayer}]"]`).value = times[prayer];
                });
            }
        }
        
        function adjustAllTimes(minutes) {
            const prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
            
            for (let i = 0; i < 7; i++) {
                prayers.forEach(prayer => {
                    const input = document.querySelector(`input[name="prayer_times[${i}][${prayer}]"]`);
                    const currentTime = input.value;
                    if (currentTime) {
                        const [hours, mins] = currentTime.split(':').map(Number);
                        const date = new Date();
                        date.setHours(hours, mins + minutes, 0, 0);
                        
                        const newHours = date.getHours().toString().padStart(2, '0');
                        const newMins = date.getMinutes().toString().padStart(2, '0');
                        input.value = `${newHours}:${newMins}`;
                    }
                });
            }
        }
        
        // Form validation
        document.getElementById('bulk-form').addEventListener('submit', function(e) {
            let hasErrors = false;
            
            for (let i = 0; i < 7; i++) {
                const prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
                const times = prayers.map(prayer => {
                    const value = document.querySelector(`input[name="prayer_times[${i}][${prayer}]"]`).value;
                    return value ? new Date('2000-01-01 ' + value) : null;
                });
                
                for (let j = 1; j < times.length; j++) {
                    if (times[j] && times[j-1] && times[j] <= times[j-1]) {
                        alert(`Error in row ${i + 1}: ${prayers[j]} must be after ${prayers[j-1]}`);
                        hasErrors = true;
                        break;
                    }
                }
                
                if (hasErrors) break;
            }
            
            if (hasErrors) {
                e.preventDefault();
            }
        });
    </script>
</x-page-layout>