<x-page-layout>
    <x-slot name="title">Gallery: {{ $imageable->title }} - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">{{ $imageable->title }}</h1>
            <p class="siks-body text-white/90">
                {{ ucfirst($type) }} Gallery • {{ $images->count() }} {{ Str::plural('image', $images->count()) }}
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6">
                @can('create', App\Models\GalleryImage::class)
                    <a href="{{ route('gallery.create', [$type . '_id' => $imageable->id]) }}" class="siks-btn-base bg-white text-siks-primary hover:bg-gray-100 focus:ring-white">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Images
                    </a>
                @endcan
                <a href="{{ route('gallery.index') }}" class="siks-btn-outline border-white text-white hover:bg-white hover:text-siks-primary">
                    Back to Gallery
                </a>
            </div>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
            <!-- Event/Fest Info -->
            <div class="siks-card p-6 mb-8">
                <div class="flex items-start space-x-6">
                    @if($imageable->image ?? $imageable->banner_image)
                        <img src="{{ asset('storage/' . ($imageable->image ?? $imageable->banner_image)) }}" 
                             alt="{{ $imageable->title }}" 
                             class="w-24 h-24 object-cover rounded-lg border border-gray-200">
                    @endif
                    <div class="flex-1">
                        <h2 class="siks-heading-3 mb-3">{{ $imageable->title }}</h2>
                        @if($imageable->description)
                            <p class="siks-body text-gray-600 mb-4">{{ Str::limit($imageable->description, 200) }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            @if($type === 'event')
                                @if($imageable->event_date)
                                    <span class="siks-badge-primary">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($imageable->event_date)->format('M j, Y') }}
                                    </span>
                                @endif
                                @if($imageable->location)
                                    <span class="siks-badge-primary">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $imageable->location }}
                                    </span>
                                @endif
                            @elseif($type === 'fest')
                                @if($imageable->start_date && $imageable->end_date)
                                    <span class="siks-badge-primary">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($imageable->start_date)->format('M j') }} - {{ \Carbon\Carbon::parse($imageable->end_date)->format('M j, Y') }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery Grid -->
            <div class="siks-card p-6">
                @if($images->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        @foreach($images as $image)
                            <div class="gallery-item relative group cursor-pointer" data-image-id="{{ $image->id }}">
                                <div class="aspect-square overflow-hidden rounded-lg bg-gray-200 siks-card-hover">
                                    <img src="{{ $image->getThumbnailUrl() }}" 
                                         alt="{{ $image->alt_text }}" 
                                         class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                                         loading="lazy">
                                </div>
                                
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 rounded-lg flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex space-x-2">
                                        <button class="lightbox-trigger bg-white bg-opacity-90 hover:bg-opacity-100 text-gray-800 p-2 rounded-full shadow-lg" data-image-id="{{ $image->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </button>
                                        @can('update', $image)
                                            <a href="{{ route('gallery.edit', $image) }}" class="bg-siks-primary bg-opacity-90 hover:bg-opacity-100 text-white p-2 rounded-full shadow-lg">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('delete', $image)
                                            <form method="POST" action="{{ route('gallery.destroy', $image) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this image?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 bg-opacity-90 hover:bg-opacity-100 text-white p-2 rounded-full shadow-lg">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                                
                                @if($image->caption)
                                    <div class="mt-2">
                                        <p class="siks-body-small text-gray-600 font-medium">{{ Str::limit($image->caption, 50) }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="siks-heading-3 mb-4">No Images Yet</h3>
                        <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                            This {{ $type }} doesn't have any images in the gallery yet.
                        </p>
                        @can('create', App\Models\GalleryImage::class)
                            <a href="{{ route('gallery.create', [$type . '_id' => $imageable->id]) }}" class="siks-btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Images
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </x-section>

    <!-- Lightbox Modal -->
    <div id="lightbox-modal" class="siks-modal hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative max-w-4xl max-h-full">
                <button id="close-lightbox" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <button id="prev-image" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button id="next-image" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                
                <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
                
                <div id="lightbox-info" class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 text-white p-4 rounded-b-lg">
                    <div id="lightbox-caption" class="font-medium text-lg"></div>
                    <div id="lightbox-details" class="text-sm text-gray-300 mt-1"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lightbox functionality (same as index page)
            const lightboxModal = document.getElementById('lightbox-modal');
            const lightboxImage = document.getElementById('lightbox-image');
            const lightboxCaption = document.getElementById('lightbox-caption');
            const lightboxDetails = document.getElementById('lightbox-details');
            const closeLightbox = document.getElementById('close-lightbox');
            const prevImage = document.getElementById('prev-image');
            const nextImage = document.getElementById('next-image');
            
            let currentImageIndex = 0;
            let galleryImages = [];
            
            // Collect all gallery images
            document.querySelectorAll('.gallery-item').forEach((item, index) => {
                galleryImages.push({
                    id: item.dataset.imageId,
                    index: index
                });
            });
            
            // Open lightbox
            document.querySelectorAll('.lightbox-trigger').forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const imageId = this.dataset.imageId;
                    const imageIndex = galleryImages.findIndex(img => img.id === imageId);
                    if (imageIndex !== -1) {
                        currentImageIndex = imageIndex;
                        loadLightboxImage(imageId);
                        lightboxModal.classList.remove('hidden');
                    }
                });
            });
            
            // Close lightbox
            closeLightbox.addEventListener('click', () => {
                lightboxModal.classList.add('hidden');
            });
            
            lightboxModal.addEventListener('click', (e) => {
                if (e.target === lightboxModal) {
                    lightboxModal.classList.add('hidden');
                }
            });
            
            // Navigation
            prevImage.addEventListener('click', () => {
                if (currentImageIndex > 0) {
                    currentImageIndex--;
                    loadLightboxImage(galleryImages[currentImageIndex].id);
                }
            });
            
            nextImage.addEventListener('click', () => {
                if (currentImageIndex < galleryImages.length - 1) {
                    currentImageIndex++;
                    loadLightboxImage(galleryImages[currentImageIndex].id);
                }
            });
            
            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (!lightboxModal.classList.contains('hidden')) {
                    switch(e.key) {
                        case 'Escape':
                            lightboxModal.classList.add('hidden');
                            break;
                        case 'ArrowLeft':
                            if (currentImageIndex > 0) {
                                currentImageIndex--;
                                loadLightboxImage(galleryImages[currentImageIndex].id);
                            }
                            break;
                        case 'ArrowRight':
                            if (currentImageIndex < galleryImages.length - 1) {
                                currentImageIndex++;
                                loadLightboxImage(galleryImages[currentImageIndex].id);
                            }
                            break;
                    }
                }
            });
            
            // Load image data
            function loadLightboxImage(imageId) {
                fetch(`/gallery/image-data/${imageId}`)
                    .then(response => response.json())
                    .then(data => {
                        lightboxImage.src = data.url;
                        lightboxImage.alt = data.alt_text;
                        lightboxCaption.textContent = data.caption || 'No caption';
                        
                        let details = `Uploaded by ${data.uploader} on ${data.uploaded_at}`;
                        if (data.file_size) {
                            details += ` • ${data.file_size}`;
                        }
                        lightboxDetails.textContent = details;
                        
                        // Update navigation button states
                        prevImage.style.display = currentImageIndex > 0 ? 'block' : 'none';
                        nextImage.style.display = currentImageIndex < galleryImages.length - 1 ? 'block' : 'none';
                    })
                    .catch(error => {
                        console.error('Error loading image data:', error);
                    });
            }
        });
    </script>
    @endpush
</x-page-layout>