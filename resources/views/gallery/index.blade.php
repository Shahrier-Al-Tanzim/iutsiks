<x-page-layout>
    <x-slot name="title">Gallery - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Gallery</h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                Explore photos from our events, activities, and community gatherings.
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-7xl mx-auto">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="siks-heading-2 mb-2">Photo Gallery</h2>
                    <p class="siks-body text-gray-600">
                        {{ $statistics['total_images'] }} {{ Str::plural('image', $statistics['total_images']) }} in gallery
                    </p>
                </div>
                @can('create', App\Models\GalleryImage::class)
                    <a href="{{ route('gallery.create') }}" class="siks-btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Upload Images
                    </a>
                @endcan
            </div>

            <!-- Filters -->
            <div class="siks-card p-6 mb-8">
                <form method="GET" action="{{ route('gallery.index') }}" class="space-y-4 lg:space-y-0 lg:grid lg:grid-cols-4 lg:gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Filter by Type</label>
                        <select name="type" id="type" class="siks-input">
                            <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>All Images</option>
                            <option value="events" {{ ($filters['type'] ?? '') === 'events' ? 'selected' : '' }}>Event Images</option>
                            <option value="fests" {{ ($filters['type'] ?? '') === 'fests' ? 'selected' : '' }}>Fest Images</option>
                            <option value="general" {{ ($filters['type'] ?? '') === 'general' ? 'selected' : '' }}>General Gallery</option>
                        </select>
                    </div>

                    <div>
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-2">Specific Event</label>
                        <select name="event_id" id="event_id" class="siks-input">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ ($filters['event_id'] ?? '') == $event->id ? 'selected' : '' }}>
                                    {{ $event->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="fest_id" class="block text-sm font-medium text-gray-700 mb-2">Specific Fest</label>
                        <select name="fest_id" id="fest_id" class="siks-input">
                            <option value="">All Fests</option>
                            @foreach($fests as $fest)
                                <option value="{{ $fest->id }}" {{ ($filters['fest_id'] ?? '') == $fest->id ? 'selected' : '' }}>
                                    {{ $fest->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="siks-btn-primary">
                            Filter
                        </button>
                        <a href="{{ route('gallery.index') }}" class="siks-btn-ghost">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Statistics -->
            <div class="siks-card p-6 mb-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                    <div>
                        <div class="text-3xl font-bold text-siks-primary mb-1">{{ $statistics['total_images'] }}</div>
                        <div class="siks-body-small text-gray-600">Total Images</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-blue-600 mb-1">{{ $statistics['event_images'] }}</div>
                        <div class="siks-body-small text-gray-600">Event Images</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-purple-600 mb-1">{{ $statistics['fest_images'] }}</div>
                        <div class="siks-body-small text-gray-600">Fest Images</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-600 mb-1">{{ $statistics['general_images'] }}</div>
                        <div class="siks-body-small text-gray-600">General Images</div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions (for admins) -->
            @can('create', App\Models\GalleryImage::class)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" id="bulk-actions" style="display: none;">
                    <div class="p-4 bg-yellow-50 border-b border-yellow-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-yellow-800">
                                <span id="selected-count">0</span> image(s) selected
                            </span>
                            <div class="space-x-2">
                                <button type="button" id="bulk-delete-btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                    Delete Selected
                                </button>
                                <button type="button" id="clear-selection-btn" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-1 px-3 rounded text-sm">
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- Gallery Grid -->
            @if($images->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-8">
                    @foreach($images as $image)
                        <x-gallery-item :image="$image" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $images->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-3 mb-4">No Images Found</h3>
                    <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                        @if(array_filter($filters))
                            Try adjusting your filters or <a href="{{ route('gallery.index') }}" class="text-siks-primary hover:underline">clear all filters</a> to see more images.
                        @else
                            We haven't uploaded any images yet. Check back soon for photos from our events and activities.
                        @endif
                    </p>
                    @can('create', App\Models\GalleryImage::class)
                        <a href="{{ route('gallery.create') }}" class="siks-btn-primary">
                            Upload First Images
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </x-section>

    <!-- Lightbox Modal -->
    <div id="lightbox-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative max-w-4xl max-h-full">
                <button id="close-lightbox" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <button id="prev-image" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button id="next-image" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                
                <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
                
                <div id="lightbox-info" class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-4">
                    <div id="lightbox-caption" class="font-medium"></div>
                    <div id="lightbox-details" class="text-sm text-gray-300 mt-1"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Delete Form -->
    @can('create', App\Models\GalleryImage::class)
        <form id="bulk-delete-form" method="POST" action="{{ route('gallery.bulk-delete') }}" style="display: none;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="image_ids" id="bulk-delete-ids">
        </form>
    @endcan

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lightbox functionality
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
                        if (data.imageable) {
                            details += ` • ${data.imageable.type}: ${data.imageable.title}`;
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
            
            @can('create', App\Models\GalleryImage::class)
            // Bulk selection functionality
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            const clearSelectionBtn = document.getElementById('clear-selection-btn');
            const bulkDeleteForm = document.getElementById('bulk-delete-form');
            const bulkDeleteIds = document.getElementById('bulk-delete-ids');
            
            let selectedImages = new Set();
            
            // Handle checkbox changes
            document.querySelectorAll('.image-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        selectedImages.add(this.value);
                    } else {
                        selectedImages.delete(this.value);
                    }
                    updateBulkActions();
                });
            });
            
            // Update bulk actions visibility and count
            function updateBulkActions() {
                const count = selectedImages.size;
                selectedCount.textContent = count;
                
                if (count > 0) {
                    bulkActions.style.display = 'block';
                } else {
                    bulkActions.style.display = 'none';
                }
            }
            
            // Clear selection
            clearSelectionBtn.addEventListener('click', function() {
                selectedImages.clear();
                document.querySelectorAll('.image-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateBulkActions();
            });
            
            // Bulk delete
            bulkDeleteBtn.addEventListener('click', function() {
                if (selectedImages.size === 0) return;
                
                if (confirm(`Are you sure you want to delete ${selectedImages.size} selected image(s)? This action cannot be undone.`)) {
                    bulkDeleteIds.value = JSON.stringify(Array.from(selectedImages));
                    bulkDeleteForm.submit();
                }
            });
            @endcan
        });
    </script>
    @endpush
</x-page-layout>