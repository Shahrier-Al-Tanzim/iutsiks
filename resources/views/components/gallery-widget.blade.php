@props(['images', 'type' => 'recent', 'title' => null, 'showViewAll' => true, 'columns' => 'grid-cols-3'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200']) }}>
    @if($title || $showViewAll)
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            @if($title)
                <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
            @else
                <h3 class="text-lg font-medium text-gray-900">
                    @switch($type)
                        @case('event')
                            Event Gallery
                            @break
                        @case('fest')
                            Fest Gallery
                            @break
                        @default
                            Recent Images
                    @endswitch
                </h3>
            @endif
            
            @if($showViewAll)
                <a href="{{ route('gallery.index') }}" class="text-sm text-green-600 hover:text-green-500 font-medium">
                    View All →
                </a>
            @endif
        </div>
    @endif
    
    <div class="p-4">
        @if($images->count() > 0)
            <div class="grid {{ $columns }} md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($images as $image)
                    <div class="gallery-widget-item relative group cursor-pointer" data-image-id="{{ $image->id }}">
                        <div class="aspect-square overflow-hidden rounded-lg bg-gray-200">
                            <img src="{{ $image->getThumbnailUrl() }}" 
                                 alt="{{ $image->alt_text }}" 
                                 class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                                 loading="lazy">
                        </div>
                        
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 rounded-lg flex items-center justify-center">
                            <button class="gallery-widget-lightbox opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-white bg-opacity-90 hover:bg-opacity-100 text-gray-800 p-2 rounded-full" 
                                    data-image-id="{{ $image->id }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($image->caption)
                            <div class="mt-1 text-xs text-gray-600 text-center">
                                <p class="truncate">{{ $image->caption }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No images available</p>
            </div>
        @endif
    </div>
</div>

<!-- Widget Lightbox Modal -->
<div id="widget-lightbox-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl max-h-full">
            <button id="widget-close-lightbox" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <button id="widget-prev-image" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <button id="widget-next-image" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <img id="widget-lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain">
            
            <div id="widget-lightbox-info" class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-4">
                <div id="widget-lightbox-caption" class="font-medium"></div>
                <div id="widget-lightbox-details" class="text-sm text-gray-300 mt-1"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we haven't already
    if (window.galleryWidgetInitialized) return;
    window.galleryWidgetInitialized = true;
    
    // Widget lightbox functionality
    const widgetLightboxModal = document.getElementById('widget-lightbox-modal');
    const widgetLightboxImage = document.getElementById('widget-lightbox-image');
    const widgetLightboxCaption = document.getElementById('widget-lightbox-caption');
    const widgetLightboxDetails = document.getElementById('widget-lightbox-details');
    const widgetCloseLightbox = document.getElementById('widget-close-lightbox');
    const widgetPrevImage = document.getElementById('widget-prev-image');
    const widgetNextImage = document.getElementById('widget-next-image');
    
    let widgetCurrentImageIndex = 0;
    let widgetGalleryImages = [];
    
    // Collect all widget gallery images
    function updateWidgetGalleryImages() {
        widgetGalleryImages = [];
        document.querySelectorAll('.gallery-widget-item').forEach((item, index) => {
            widgetGalleryImages.push({
                id: item.dataset.imageId,
                index: index
            });
        });
    }
    
    // Initial collection
    updateWidgetGalleryImages();
    
    // Open widget lightbox
    document.addEventListener('click', function(e) {
        if (e.target.closest('.gallery-widget-lightbox')) {
            e.preventDefault();
            e.stopPropagation();
            
            const trigger = e.target.closest('.gallery-widget-lightbox');
            const imageId = trigger.dataset.imageId;
            
            // Update images collection in case DOM changed
            updateWidgetGalleryImages();
            
            const imageIndex = widgetGalleryImages.findIndex(img => img.id === imageId);
            if (imageIndex !== -1) {
                widgetCurrentImageIndex = imageIndex;
                loadWidgetLightboxImage(imageId);
                widgetLightboxModal.classList.remove('hidden');
            }
        }
    });
    
    // Close widget lightbox
    if (widgetCloseLightbox) {
        widgetCloseLightbox.addEventListener('click', () => {
            widgetLightboxModal.classList.add('hidden');
        });
    }
    
    if (widgetLightboxModal) {
        widgetLightboxModal.addEventListener('click', (e) => {
            if (e.target === widgetLightboxModal) {
                widgetLightboxModal.classList.add('hidden');
            }
        });
    }
    
    // Widget navigation
    if (widgetPrevImage) {
        widgetPrevImage.addEventListener('click', () => {
            if (widgetCurrentImageIndex > 0) {
                widgetCurrentImageIndex--;
                loadWidgetLightboxImage(widgetGalleryImages[widgetCurrentImageIndex].id);
            }
        });
    }
    
    if (widgetNextImage) {
        widgetNextImage.addEventListener('click', () => {
            if (widgetCurrentImageIndex < widgetGalleryImages.length - 1) {
                widgetCurrentImageIndex++;
                loadWidgetLightboxImage(widgetGalleryImages[widgetCurrentImageIndex].id);
            }
        });
    }
    
    // Widget keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!widgetLightboxModal || widgetLightboxModal.classList.contains('hidden')) return;
        
        switch(e.key) {
            case 'Escape':
                widgetLightboxModal.classList.add('hidden');
                break;
            case 'ArrowLeft':
                if (widgetCurrentImageIndex > 0) {
                    widgetCurrentImageIndex--;
                    loadWidgetLightboxImage(widgetGalleryImages[widgetCurrentImageIndex].id);
                }
                break;
            case 'ArrowRight':
                if (widgetCurrentImageIndex < widgetGalleryImages.length - 1) {
                    widgetCurrentImageIndex++;
                    loadWidgetLightboxImage(widgetGalleryImages[widgetCurrentImageIndex].id);
                }
                break;
        }
    });
    
    // Load widget image data
    function loadWidgetLightboxImage(imageId) {
        fetch(`/gallery/image-data/${imageId}`)
            .then(response => response.json())
            .then(data => {
                if (widgetLightboxImage) {
                    widgetLightboxImage.src = data.url;
                    widgetLightboxImage.alt = data.alt_text;
                }
                
                if (widgetLightboxCaption) {
                    widgetLightboxCaption.textContent = data.caption || 'No caption';
                }
                
                if (widgetLightboxDetails) {
                    let details = `Uploaded by ${data.uploader} on ${data.uploaded_at}`;
                    if (data.file_size) {
                        details += ` • ${data.file_size}`;
                    }
                    if (data.imageable) {
                        details += ` • ${data.imageable.type}: ${data.imageable.title}`;
                    }
                    widgetLightboxDetails.textContent = details;
                }
                
                // Update navigation button states
                if (widgetPrevImage) {
                    widgetPrevImage.style.display = widgetCurrentImageIndex > 0 ? 'block' : 'none';
                }
                if (widgetNextImage) {
                    widgetNextImage.style.display = widgetCurrentImageIndex < widgetGalleryImages.length - 1 ? 'block' : 'none';
                }
            })
            .catch(error => {
                console.error('Error loading widget image data:', error);
            });
    }
});
</script>
@endpush