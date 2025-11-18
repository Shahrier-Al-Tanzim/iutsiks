@props(['compact' => false, 'showLocation' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md']) }}>
    <div id="prayer-times-widget" class="p-4">
        <!-- Loading State -->
        <div id="widget-loading" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
            <p class="text-sm text-gray-600 mt-2">Loading prayer times...</p>
        </div>

        <!-- Widget Content (Hidden initially) -->
        <div id="widget-content" class="hidden">
            <!-- Header -->
            <div class="text-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Prayer Times</h3>
                <p id="widget-date" class="text-sm text-gray-600"></p>
                @if($showLocation)
                    <p id="widget-location" class="text-xs text-gray-500"></p>
                @endif
            </div>

            <!-- Current/Next Prayer Highlight -->
            <div id="current-prayer-section" class="hidden bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                <div class="text-center">
                    <p id="current-prayer-label" class="text-sm font-medium text-green-800"></p>
                    <p id="current-prayer-time" class="text-lg font-bold text-green-900"></p>
                    <p id="current-prayer-countdown" class="text-xs text-green-700"></p>
                </div>
            </div>

            <!-- Prayer Times Grid -->
            @if($compact)
                <!-- Compact Layout -->
                <div class="space-y-2">
                    <div class="grid grid-cols-5 gap-2 text-xs">
                        <div class="text-center font-medium text-gray-600">Fajr</div>
                        <div class="text-center font-medium text-gray-600">Dhuhr</div>
                        <div class="text-center font-medium text-gray-600">Asr</div>
                        <div class="text-center font-medium text-gray-600">Maghrib</div>
                        <div class="text-center font-medium text-gray-600">Isha</div>
                    </div>
                    <div id="prayer-times-compact" class="grid grid-cols-5 gap-2 text-xs">
                        <!-- Times will be populated by JavaScript -->
                    </div>
                </div>
            @else
                <!-- Full Layout -->
                <div id="prayer-times-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <!-- Prayer time cards will be populated by JavaScript -->
                </div>
            @endif

            <!-- Footer -->
            <div class="text-center mt-4 pt-3 border-t border-gray-200">
                <a href="{{ route('prayer-times.index') }}" 
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View Full Prayer Times
                </a>
            </div>
        </div>

        <!-- Error State -->
        <div id="widget-error" class="hidden text-center py-4">
            <div class="text-red-500 mb-2">
                <svg class="mx-auto h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm text-gray-600">Unable to load prayer times</p>
            <button onclick="loadPrayerTimes()" class="text-sm text-blue-600 hover:text-blue-800 mt-2">
                Try Again
            </button>
        </div>
    </div>
</div>

<script>
    let prayerTimesData = null;
    let updateInterval = null;

    // Load prayer times when widget is initialized
    document.addEventListener('DOMContentLoaded', function() {
        loadPrayerTimes();
        
        // Update every minute to keep current/next prayer accurate
        updateInterval = setInterval(updateCurrentPrayer, 60000);
    });

    // Clean up interval when page is unloaded
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });

    async function loadPrayerTimes() {
        try {
            showLoading();
            
            const response = await fetch('{{ route('prayer-times.widget') }}');
            const data = await response.json();
            
            if (data.prayer_times) {
                prayerTimesData = data;
                renderPrayerTimes(data);
                showContent();
            } else {
                showNoPrayerTimes();
            }
        } catch (error) {
            console.error('Error loading prayer times:', error);
            showError();
        }
    }

    function renderPrayerTimes(data) {
        const { prayer_times, current_prayer, next_prayer } = data;
        
        // Update header
        document.getElementById('widget-date').textContent = prayer_times.date;
        @if($showLocation)
            document.getElementById('widget-location').textContent = prayer_times.location;
        @endif

        // Update current/next prayer section
        updateCurrentPrayerSection(current_prayer, next_prayer);

        @if($compact)
            renderCompactLayout(prayer_times.prayers);
        @else
            renderFullLayout(prayer_times.prayers, current_prayer, next_prayer);
        @endif
    }

    function renderCompactLayout(prayers) {
        const container = document.getElementById('prayer-times-compact');
        container.innerHTML = '';

        Object.values(prayers).forEach(prayer => {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'text-center font-medium text-gray-900';
            timeDiv.textContent = prayer.formatted;
            container.appendChild(timeDiv);
        });
    }

    function renderFullLayout(prayers, currentPrayer, nextPrayer) {
        const container = document.getElementById('prayer-times-grid');
        container.innerHTML = '';

        Object.entries(prayers).forEach(([key, prayer]) => {
            const isCurrent = currentPrayer && currentPrayer.name.toLowerCase() === key;
            const isNext = nextPrayer && nextPrayer.name.toLowerCase() === key;
            
            const card = document.createElement('div');
            card.className = `text-center p-3 rounded-lg border-2 ${
                isCurrent ? 'border-green-500 bg-green-50' : (
                isNext ? 'border-blue-500 bg-blue-50' : 
                'border-gray-200 bg-gray-50')
            }`;
            
            card.innerHTML = `
                <h4 class="text-sm font-semibold text-gray-800 mb-1">${prayer.name}</h4>
                <p class="text-lg font-bold ${
                    isCurrent ? 'text-green-700' : (
                    isNext ? 'text-blue-700' : 
                    'text-gray-900')
                }">${prayer.formatted}</p>
                ${isCurrent ? '<span class="inline-block mt-1 px-2 py-1 bg-green-200 text-green-800 text-xs font-medium rounded-full">Current</span>' : ''}
                ${isNext ? '<span class="inline-block mt-1 px-2 py-1 bg-blue-200 text-blue-800 text-xs font-medium rounded-full">Next</span>' : ''}
            `;
            
            container.appendChild(card);
        });
    }

    function updateCurrentPrayerSection(currentPrayer, nextPrayer) {
        const section = document.getElementById('current-prayer-section');
        const label = document.getElementById('current-prayer-label');
        const time = document.getElementById('current-prayer-time');
        const countdown = document.getElementById('current-prayer-countdown');

        if (currentPrayer || nextPrayer) {
            const prayer = currentPrayer || nextPrayer;
            const isCurrent = !!currentPrayer;
            
            label.textContent = isCurrent ? 'Current Prayer Time' : 'Next Prayer';
            time.textContent = `${prayer.name} - ${prayer.formatted_time}`;
            
            if (prayer.time_until) {
                countdown.textContent = isCurrent ? `Time remaining: ${prayer.time_until}` : `In ${prayer.time_until}`;
            } else {
                countdown.textContent = '';
            }
            
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    function updateCurrentPrayer() {
        if (prayerTimesData) {
            // Re-fetch current prayer status
            loadPrayerTimes();
        }
    }

    function showLoading() {
        document.getElementById('widget-loading').classList.remove('hidden');
        document.getElementById('widget-content').classList.add('hidden');
        document.getElementById('widget-error').classList.add('hidden');
    }

    function showContent() {
        document.getElementById('widget-loading').classList.add('hidden');
        document.getElementById('widget-content').classList.remove('hidden');
        document.getElementById('widget-error').classList.add('hidden');
    }

    function showError() {
        document.getElementById('widget-loading').classList.add('hidden');
        document.getElementById('widget-content').classList.add('hidden');
        document.getElementById('widget-error').classList.remove('hidden');
    }

    function showNoPrayerTimes() {
        document.getElementById('widget-loading').classList.add('hidden');
        document.getElementById('widget-error').classList.add('hidden');
        
        const content = document.getElementById('widget-content');
        content.innerHTML = `
            <div class="text-center py-4">
                <div class="mb-2">
                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-600">No prayer times available for today</p>
                <a href="{{ route('prayer-times.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">
                    View Prayer Times Page
                </a>
            </div>
        `;
        content.classList.remove('hidden');
    }
</script>