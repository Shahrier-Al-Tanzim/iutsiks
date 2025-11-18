@php
    // Fetch data for the home page
    $upcomingEvents = \App\Models\Event::with(['fest', 'author'])
        ->where('status', 'published')
        ->where('event_date', '>=', now())
        ->orderBy('event_date')
        ->limit(3)
        ->get();
    
    $featuredBlogs = \App\Models\Blog::with('author')
        ->latest()
        ->limit(3)
        ->get();
    
    $prayerTimeService = new \App\Services\PrayerTimeService();
    $todaysPrayerTimes = $prayerTimeService->getTodaysPrayerTimes();
@endphp

<x-page-layout>
    <x-slot name="title">Welcome to SIKS - Islamic Society of IUT</x-slot>
    
    <!-- Hero Section -->
    <x-section background="primary" padding="large">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-6">
                Welcome to Islamic Society (SIKS)
            </h1>
            <p class="siks-body text-white/90 mb-8 max-w-3xl mx-auto">
                The Islamic Society of Islamic University of Technology (IUT) is dedicated to promoting Islamic values, 
                organizing educational events, fostering community among students, and serving as a bridge between 
                academic excellence and spiritual growth.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('events.index') }}" class="siks-btn-base bg-white text-siks-darker hover:bg-gray-100 focus:ring-white">
                    Explore Events
                </a>
                <a href="{{ route('register') }}" class="siks-btn-outline border-white text-white hover:bg-white hover:text-siks-darker">
                    Join Our Community
                </a>
            </div>
        </div>
    </x-section>

    <!-- About SIKS Section -->
    <x-section title="About SIKS" subtitle="Learn more about our mission and values">
        <div class="siks-grid-2 gap-12 items-center">
            <div>
                <h3 class="siks-heading-3 mb-4">Our Mission</h3>
                <p class="siks-body text-gray-600 mb-6">
                    SIKS serves as the spiritual and cultural hub for Muslim students at IUT, providing a platform for 
                    Islamic education, community building, and personal development. We organize regular events, lectures, 
                    and activities that strengthen faith while promoting academic excellence.
                </p>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-siks-darker rounded-full mr-3"></div>
                        <span class="siks-body text-gray-700">Promoting Islamic values and teachings</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-siks-darker rounded-full mr-3"></div>
                        <span class="siks-body text-gray-700">Building a strong Muslim community at IUT</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-siks-darker rounded-full mr-3"></div>
                        <span class="siks-body text-gray-700">Supporting academic and spiritual growth</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-siks-darker rounded-full mr-3"></div>
                        <span class="siks-body text-gray-700">Organizing educational and cultural events</span>
                    </div>
                </div>
            </div>
            <div class="siks-card p-8 text-center">
                <div class="w-20 h-20 bg-siks-darker/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-siks-darker" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="siks-heading-4 mb-3">Established Excellence</h4>
                <p class="siks-body text-gray-600">
                    Serving the IUT community with dedication, organizing impactful events, and fostering 
                    spiritual growth among students since our establishment.
                </p>
            </div>
        </div>
    </x-section>

    <!-- Upcoming Events Section -->
    @if($upcomingEvents->count() > 0)
        <x-section background="gray" title="Upcoming Events" subtitle="Don't miss these exciting upcoming events">
            <div class="siks-grid-3">
                @foreach($upcomingEvents as $event)
                    <x-event-card :event="$event" />
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('events.index') }}" class="siks-btn-primary">
                    View All Events
                </a>
            </div>
        </x-section>
    @endif

    <!-- Featured Blogs Section -->
    @if($featuredBlogs->count() > 0)
        <x-section title="Latest from Our Blog" subtitle="Stay updated with our latest articles and insights">
            <div class="siks-grid-3">
                @foreach($featuredBlogs as $blog)
                    <x-blog-card :blog="$blog" />
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('blogs.index') }}" class="siks-btn-outline">
                    Read More Articles
                </a>
            </div>
        </x-section>
    @endif

    <!-- Prayer Times Widget -->
    @if($todaysPrayerTimes)
        <x-section background="primary">
            <div class="text-center mb-8">
                <h2 class="siks-heading-2 text-white mb-4">Today's Prayer Times</h2>
                <p class="siks-body text-white/90">IOT Masjid - {{ now()->format('l, F j, Y') }}</p>
            </div>
            <x-prayer-times-widget :prayerTimes="$todaysPrayerTimes" theme="dark" />
            <div class="text-center mt-8">
                <a href="{{ route('prayer-times.index') }}" class="siks-btn-base bg-white text-siks-primary hover:bg-gray-100">
                    View Full Prayer Schedule
                </a>
            </div>
        </x-section>
    @endif

    <!-- What We Offer Section -->
    <x-section title="What We Offer" subtitle="Discover the various ways SIKS serves the IUT community">
        <div class="siks-grid-4">
            <!-- Events -->
            <div class="siks-card text-center p-6">
                <div class="w-16 h-16 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-siks-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="siks-heading-4 mb-3">Events & Programs</h3>
                <p class="siks-body text-gray-600 text-sm">
                    Regular lectures, competitions, workshops, and community gatherings.
                </p>
            </div>

            <!-- Prayer Times -->
            <div class="siks-card text-center p-6">
                <div class="w-16 h-16 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-siks-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="siks-heading-4 mb-3">Prayer Times</h3>
                <p class="siks-body text-gray-600 text-sm">
                    Daily prayer times for the IOT Masjid updated regularly.
                </p>
            </div>

            <!-- Community -->
            <div class="siks-card text-center p-6">
                <div class="w-16 h-16 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-siks-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
                <h3 class="siks-heading-4 mb-3">Community</h3>
                <p class="siks-body text-gray-600 text-sm">
                    Connect with fellow students and build lasting friendships.
                </p>
            </div>

            <!-- Gallery -->
            <div class="siks-card text-center p-6">
                <div class="w-16 h-16 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-siks-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="siks-heading-4 mb-3">Gallery</h3>
                <p class="siks-body text-gray-600 text-sm">
                    View photos from past events and society activities.
                </p>
            </div>
        </div>
    </x-section>

    <!-- Call to Action Section -->
    <x-section background="gray">
        <div class="text-center">
            <h2 class="siks-heading-2 mb-4">Ready to Join Our Community?</h2>
            <p class="siks-body text-gray-600 mb-8 max-w-2xl mx-auto">
                Become part of the SIKS family and embark on a journey of spiritual growth, academic excellence, 
                and meaningful connections with fellow Muslim students at IUT.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                    <a href="{{ route('register') }}" class="siks-btn-primary">
                        Register Now
                    </a>
                    <a href="{{ route('contact') }}" class="siks-btn-outline">
                        Contact Us
                    </a>
                @else
                    <a href="{{ route('events.index') }}" class="siks-btn-primary">
                        Browse Events
                    </a>
                    <a href="{{ route('registrations.history') }}" class="siks-btn-outline">
                        My Registrations
                    </a>
                @endguest
            </div>
        </div>
    </x-section>
</x-page-layout>