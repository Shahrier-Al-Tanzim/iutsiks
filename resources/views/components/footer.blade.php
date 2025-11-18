@props(['minimal' => false])

<footer class="bg-siks-secondary text-white">
    @if(!$minimal)
        <!-- Main Footer Content -->
        <div class="siks-container py-12">
            <div class="siks-grid-4">
                <!-- About Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Islamic Society (SIKS)</h3>
                    <p class="text-gray-300 mb-4">
                        The Islamic Society of Islamic University of Technology (IUT) is dedicated to promoting Islamic values, 
                        organizing educational events, and fostering community among students.
                    </p>
                    <div class="flex space-x-4">
                        <!-- Social Media Links -->
                        <a href="#" class="text-gray-300 hover:text-siks-light transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-siks-light transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-siks-light transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.221.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.747 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.624 0 11.99-5.367 11.99-11.99C24.007 5.367 18.641.001.017.001z.017"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('events.index') }}" class="text-gray-300 hover:text-siks-light transition-colors">Events</a></li>
                        <li><a href="{{ route('blogs.index') }}" class="text-gray-300 hover:text-siks-light transition-colors">Blogs</a></li>
                        <li><a href="{{ route('prayer-times.index') }}" class="text-gray-300 hover:text-siks-light transition-colors">Prayer Times</a></li>
                        <li><a href="{{ route('gallery.index') }}" class="text-gray-300 hover:text-siks-light transition-colors">Gallery</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-siks-light transition-colors">Contact</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Resources</h3>
                    <ul class="space-y-2">
                        @auth
                            <li><a href="{{ route('registrations.history') }}" class="text-gray-300 hover:text-siks-light transition-colors">My Registrations</a></li>
                            <li><a href="{{ route('profile.edit') }}" class="text-gray-300 hover:text-siks-light transition-colors">Profile</a></li>
                        @else
                            <li><a href="{{ route('register') }}" class="text-gray-300 hover:text-siks-light transition-colors">Join Us</a></li>
                            <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-siks-light transition-colors">Login</a></li>
                        @endauth
                        <li><a href="#" class="text-gray-300 hover:text-siks-light transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-siks-light transition-colors">Support</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <div class="space-y-2 text-gray-300">
                        <p class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            Islamic University of Technology<br>
                            Board Bazar, Gazipur-1704<br>
                            Bangladesh
                        </p>
                        <p class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                            siks@iut-dhaka.edu
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bottom Bar -->
    <div class="border-t border-gray-600">
        <div class="siks-container py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-300 text-sm">
                    © {{ date('Y') }} Islamic Society (SIKS), Islamic University of Technology. All rights reserved.
                </p>
                <div class="flex space-x-6 mt-2 md:mt-0">
                    <a href="#" class="text-gray-300 hover:text-siks-light text-sm transition-colors">Privacy Policy</a>
                    <a href="#" class="text-gray-300 hover:text-siks-light text-sm transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>