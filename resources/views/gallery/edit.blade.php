<x-page-layout>
    <x-slot name="title">Edit Image - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Edit Image</h1>
            <p class="siks-body text-white/90">
                Update image details and settings
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-6xl mx-auto">
            <div class="siks-card p-6">
                <div class="siks-grid-2 gap-8">
                    <!-- Image Preview -->
                    <div>
                        <h3 class="siks-heading-3 mb-6">Current Image</h3>
                        <div class="aspect-square overflow-hidden rounded-lg bg-gray-200 mb-6">
                            <img src="{{ $image->getImageUrl() }}" 
                                 alt="{{ $image->alt_text }}" 
                                 class="w-full h-full object-cover">
                        </div>
                        
                        <div class="siks-card p-4 space-y-3">
                            <div class="flex justify-between">
                                <span class="siks-body font-medium">Uploaded by:</span>
                                <span class="siks-body">{{ $image->uploader->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="siks-body font-medium">Upload date:</span>
                                <span class="siks-body">{{ $image->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="siks-body font-medium">File size:</span>
                                <span class="siks-body">{{ $image->getFormattedFileSize() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="siks-body font-medium">Associated with:</span>
                                <span class="siks-body">
                                    @if($image->imageable)
                                        {{ class_basename($image->imageable) }} - {{ $image->imageable->title }}
                                    @else
                                        General Gallery
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <div>
                        <h3 class="siks-heading-3 mb-6">Edit Details</h3>
                        
                        <form method="POST" action="{{ route('gallery.update', $image) }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <!-- Caption -->
                            <div class="siks-form-group">
                                <label class="siks-label" for="caption">Caption</label>
                                <textarea id="caption" 
                                          name="caption" 
                                          rows="3" 
                                          class="siks-textarea"
                                          placeholder="Enter a caption for this image...">{{ old('caption', $image->caption) }}</textarea>
                                @error('caption')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Alt Text -->
                            <div class="siks-form-group">
                                <label class="siks-label" for="alt_text">
                                    Alt Text
                                    <span class="siks-body-small text-gray-500">(for accessibility)</span>
                                </label>
                                <input type="text" 
                                       id="alt_text" 
                                       name="alt_text" 
                                       value="{{ old('alt_text', $image->alt_text) }}"
                                       class="siks-input"
                                       placeholder="Describe what's in the image...">
                                @error('alt_text')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Association -->
                            <div class="siks-form-group">
                                <label class="siks-label">Change Association</label>
                                <div class="space-y-4">
                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                   name="association_type" 
                                                   value="general" 
                                                   class="form-radio text-siks-primary"
                                                   {{ (!$image->imageable) ? 'checked' : '' }}>
                                            <span class="ml-2 siks-body">General Gallery</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                   name="association_type" 
                                                   value="event" 
                                                   class="form-radio text-siks-primary"
                                                   {{ ($image->belongsToEvent()) ? 'checked' : '' }}>
                                            <span class="ml-2 siks-body">Specific Event</span>
                                        </label>
                                        <select name="association_id" 
                                                id="event-select" 
                                                class="mt-2 ml-6 siks-select"
                                                {{ !$image->belongsToEvent() ? 'disabled' : '' }}>
                                            <option value="">Select an event...</option>
                                            @foreach($events as $event)
                                                <option value="{{ $event->id }}" 
                                                        {{ ($image->belongsToEvent() && $image->imageable_id == $event->id) ? 'selected' : '' }}>
                                                    {{ $event->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="inline-flex items-center">
                                            <input type="radio" 
                                                   name="association_type" 
                                                   value="fest" 
                                                   class="form-radio text-siks-primary"
                                                   {{ ($image->belongsToFest()) ? 'checked' : '' }}>
                                            <span class="ml-2 siks-body">Specific Fest</span>
                                        </label>
                                        <select name="association_id" 
                                                id="fest-select" 
                                                class="mt-2 ml-6 siks-select"
                                                {{ !$image->belongsToFest() ? 'disabled' : '' }}>
                                            <option value="">Select a fest...</option>
                                            @foreach($fests as $fest)
                                                <option value="{{ $fest->id }}" 
                                                        {{ ($image->belongsToFest() && $image->imageable_id == $fest->id) ? 'selected' : '' }}>
                                                    {{ $fest->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @error('association_type')
                                    <p class="siks-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row justify-between gap-4 pt-6 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <button type="submit" class="siks-btn-primary">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Update Image
                                    </button>
                                    <a href="{{ route('gallery.index') }}" class="siks-btn-ghost">
                                        Cancel
                                    </a>
                                </div>
                                
                                @can('delete', $image)
                                    <form method="POST" action="{{ route('gallery.destroy', $image) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this image? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete Image
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Association type handling
            const associationRadios = document.querySelectorAll('input[name="association_type"]');
            const eventSelect = document.getElementById('event-select');
            const festSelect = document.getElementById('fest-select');
            
            associationRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'event') {
                        eventSelect.disabled = false;
                        festSelect.disabled = true;
                        festSelect.value = '';
                        eventSelect.name = 'association_id';
                        festSelect.name = '';
                    } else if (this.value === 'fest') {
                        festSelect.disabled = false;
                        eventSelect.disabled = true;
                        eventSelect.value = '';
                        festSelect.name = 'association_id';
                        eventSelect.name = '';
                    } else {
                        eventSelect.disabled = true;
                        festSelect.disabled = true;
                        eventSelect.value = '';
                        festSelect.value = '';
                        eventSelect.name = '';
                        festSelect.name = '';
                    }
                });
            });
            
            // Trigger initial state
            const checkedRadio = document.querySelector('input[name="association_type"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
            }
        });
    </script>
    @endpush
</x-app-layout>