@props(['image', 'showCaption' => true, 'showActions' => false])

<div class="group relative overflow-hidden rounded-lg bg-gray-100 aspect-square">
    <!-- Optimized Image -->
    <x-optimized-image
        :src="$image->getThumbnailUrl('medium')"
        :srcset="$image->getResponsiveSrcset()"
        :alt="$image->alt_text ?? $image->caption ?? 'Gallery image'"
        :responsive="true"
        :lazy="true"
        :placeholder="true"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        sizes="(max-width: 640px) 100vw, (max-width: 768px) 50vw, (max-width: 1024px) 33vw, 25vw"
    />
    
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-center justify-center">
        <!-- View Button -->
        <button 
            type="button"
            onclick="openLightbox('{{ asset('storage/' . $image->image_path) }}', '{{ addslashes($image->caption ?? '') }}')"
            class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 siks-btn-primary"
        >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
            </svg>
            View
        </button>
    </div>
    
    <!-- Admin Actions -->
    @if($showActions)
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="flex gap-1">
                @can('update', $image)
                    <button 
                        type="button"
                        onclick="editImage({{ $image->id }})"
                        class="siks-btn-base bg-yellow-500 text-white hover:bg-yellow-600 p-2"
                        title="Edit Caption"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                    </button>
                @endcan
                
                @can('delete', $image)
                    <form action="{{ route('gallery.destroy', $image) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="siks-btn-base bg-red-500 text-white hover:bg-red-600 p-2"
                            onclick="return confirm('Delete this image?')"
                            title="Delete Image"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zM12 7a1 1 0 012 0v4a1 1 0 11-2 0V7z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    @endif
    
    <!-- Caption -->
    @if($showCaption && $image->caption)
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
            <p class="text-white text-sm">{{ $image->caption }}</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function openLightbox(imageSrc, caption) {
    // Create lightbox modal
    const lightbox = document.createElement('div');
    lightbox.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90';
    lightbox.innerHTML = `
        <div class="relative max-w-4xl max-h-full p-4">
            <img src="${imageSrc}" alt="${caption}" class="max-w-full max-h-full object-contain">
            ${caption ? `<p class="text-white text-center mt-4">${caption}</p>` : ''}
            <button onclick="this.closest('.fixed').remove()" class="absolute top-4 right-4 text-white hover:text-gray-300">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    `;
    
    // Close on click outside
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            lightbox.remove();
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            lightbox.remove();
        }
    });
    
    document.body.appendChild(lightbox);
}

function editImage(imageId) {
    // Implementation for editing image caption
    const caption = prompt('Enter new caption:');
    if (caption !== null) {
        // Submit form or make AJAX request to update caption
        console.log('Update image', imageId, 'with caption:', caption);
    }
}
</script>
@endpush