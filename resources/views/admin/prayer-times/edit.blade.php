<x-page-layout>
    <x-slot name="title">Admin - SIKS</x-slot>
    

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('admin.prayer-times.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                        <!-- Date Display -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                Setting prayer times for {{ $date->format('l, F j, Y') }}
                            </h3>
                            @if($date->isToday())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Today
                                </span>
                            @elseif($date->isTomorrow())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Tomorrow
                                </span>
                            @elseif($date->isPast())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Past Date
                                </span>
                            @endif
                        </div>

                        <!-- Prayer Times Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
                            <!-- Fajr -->
                            <div>
                                <x-input-label for="fajr" :value="__('Fajr')" />
                                <x-text-input id="fajr" 
                                            class="block mt-1 w-full" 
                                            type="time" 
                                            name="fajr" 
                                            :value="old('fajr', $prayerTimes ? substr($prayerTimes->fajr, 0, 5) : '05:30')" 
                                            required />
                                <x-input-error :messages="$errors->get('fajr')" class="mt-2" />
                            </div>

                            <!-- Dhuhr -->
                            <div>
                                <x-input-label for="dhuhr" :value="__('Dhuhr')" />
                                <x-text-input id="dhuhr" 
                                            class="block mt-1 w-full" 
                                            type="time" 
                                            name="dhuhr" 
                                            :value="old('dhuhr', $prayerTimes ? substr($prayerTimes->dhuhr, 0, 5) : '12:15')" 
                                            required />
                                <x-input-error :messages="$errors->get('dhuhr')" class="mt-2" />
                            </div>

                            <!-- Asr -->
                            <div>
                                <x-input-label for="asr" :value="__('Asr')" />
                                <x-text-input id="asr" 
                                            class="block mt-1 w-full" 
                                            type="time" 
                                            name="asr" 
                                            :value="old('asr', $prayerTimes ? substr($prayerTimes->asr, 0, 5) : '15:45')" 
                                            required />
                                <x-input-error :messages="$errors->get('asr')" class="mt-2" />
                            </div>

                            <!-- Maghrib -->
                            <div>
                                <x-input-label for="maghrib" :value="__('Maghrib')" />
                                <x-text-input id="maghrib" 
                                            class="block mt-1 w-full" 
                                            type="time" 
                                            name="maghrib" 
                                            :value="old('maghrib', $prayerTimes ? substr($prayerTimes->maghrib, 0, 5) : '18:30')" 
                                            required />
                                <x-input-error :messages="$errors->get('maghrib')" class="mt-2" />
                            </div>

                            <!-- Isha -->
                            <div>
                                <x-input-label for="isha" :value="__('Isha')" />
                                <x-text-input id="isha" 
                                            class="block mt-1 w-full" 
                                            type="time" 
                                            name="isha" 
                                            :value="old('isha', $prayerTimes ? substr($prayerTimes->isha, 0, 5) : '19:45')" 
                                            required />
                                <x-input-error :messages="$errors->get('isha')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-6">
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input id="location" 
                                        class="block mt-1 w-full" 
                                        type="text" 
                                        name="location" 
                                        :value="old('location', $prayerTimes ? $prayerTimes->location : 'IOT Masjid')" 
                                        placeholder="IOT Masjid" />
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" 
                                    name="notes" 
                                    rows="3" 
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="Any special notes about these prayer times...">{{ old('notes', $prayerTimes ? $prayerTimes->notes : '') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Time Validation Help -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-800 mb-2">Time Validation Rules:</h4>
                            <ul class="text-blue-700 text-sm space-y-1">
                                <li>• Prayer times must be in chronological order (Fajr → Dhuhr → Asr → Maghrib → Isha)</li>
                                <li>• Use 24-hour format (e.g., 05:30 for 5:30 AM, 17:30 for 5:30 PM)</li>
                                <li>• Each prayer time must be later than the previous one</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-between">
                            <div class="flex space-x-4">
                                <x-primary-button>
                                    {{ __('Save Prayer Times') }}
                                </x-primary-button>
                                
                                <a href="{{ route('admin.prayer-times.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Cancel
                                </a>
                            </div>

                            @if($prayerTimes && !$date->isToday())
                                <form method="POST" action="{{ route('admin.prayer-times.destroy', $date->format('Y-m-d')) }}" 
                                      class="inline" onsubmit="return confirm('Are you sure you want to delete prayer times for {{ $date->format('F j, Y') }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button type="submit">
                                        {{ __('Delete Prayer Times') }}
                                    </x-danger-button>
                                </form>
                            @endif
                        </div>
                    </form>

                    <!-- Preview Section -->
                    @if($prayerTimes)
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4">Current Prayer Times Preview:</h4>
                            <div class="grid grid-cols-5 gap-4">
                                @foreach(['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as $prayer)
                                    <div class="text-center bg-gray-50 rounded-lg p-3">
                                        <div class="font-medium text-gray-800 capitalize">{{ $prayer }}</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ \Carbon\Carbon::parse($prayerTimes->{$prayer})->format('g:i A') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for time validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
            
            timeInputs.forEach((prayer, index) => {
                const input = document.getElementById(prayer);
                input.addEventListener('change', function() {
                    validateTimeSequence();
                });
            });
            
            function validateTimeSequence() {
                const times = timeInputs.map(prayer => {
                    const value = document.getElementById(prayer).value;
                    return value ? new Date('2000-01-01 ' + value) : null;
                });
                
                for (let i = 1; i < times.length; i++) {
                    if (times[i] && times[i-1] && times[i] <= times[i-1]) {
                        document.getElementById(timeInputs[i]).setCustomValidity(
                            `${timeInputs[i]} must be after ${timeInputs[i-1]}`
                        );
                        return;
                    } else {
                        document.getElementById(timeInputs[i]).setCustomValidity('');
                    }
                }
            }
        });
    </script>
</x-page-layout>