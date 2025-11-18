<x-page-layout>
    <x-slot name="title">Upload Images - SIKS Gallery</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Upload Images</h1>
            <p class="siks-body text-white/90">Add new images to the SIKS gallery</p>
        </div>
    </x-section>

    <!-- Form Section -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="siks-heading-3">Image Upload</h2>
                    <a href="{{ route('gallery.index') }}" class="siks-btn-ghost">
                        Back to Gallery
                    </a>
                </div>
                    <form method="POST" action="{{ route('gallery.store') }}" enctype="multipart/form-data" id="upload-form">
                        @csrf

                        <!-- Association Selection -->
                        <div class="mb-8">
                            <label class="siks-label mb-4">Associate with</label>
                            <div class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="association_type" value="general" class="w-4 h-4 text-siks-primary border-gray-300 focus:ring-siks-primary" {{ (!$selectedEvent && !$selectedFest) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="font-medium text-gray-900">General Gallery</span>
                                            <p class="text-sm text-gray-600">Not associated with any specific event or fest</p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="flex items-center cursor-pointer mb-3">
                                        <input type="radio" name="association_type" value="event" class="w-4 h-4 text-siks-primary border-gray-300 focus:ring-siks-primary" {{ $selectedEvent ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="font-medium text-gray-900">Specific Event</span>
                                            <p class="text-sm text-gray-600">Associate with a particular event</p>
                                        </div>
                                    </label>
                                    <select name="association_id" id="event-select" class="siks-select ml-7" {{ !$selectedEvent ? 'disabled' : '' }}>
                                        <option value="">Select an event...</option>
                                        @foreach($events as $event)
                                            <option value="{{ $event->id }}" {{ $selectedEvent == $event->id ? 'selected' : '' }}>
                                                {{ $event->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <label class="flex items-center cursor-pointer mb-3">
                                        <input type="radio" name="association_type" value="fest" class="w-4 h-4 text-siks-primary border-gray-300 focus:ring-siks-primary" {{ $selectedFest ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <span class="font-medium text-gray-900">Specific Fest</span>
                                            <p class="text-sm text-gray-600">Associate with a particular fest</p>
                                        </div>
                                    </label>
                                    <select name="association_id" id="fest-select" class="siks-select ml-7" {{ !$selectedFest ? 'disabled' : '' }}>
                                        <option value="">Select a fest...</option>
                                        @foreach($fests as $fest)
                                            <option value="{{ $fest->id }}" {{ $selectedFest == $fest->id ? 'selected' : '' }}>
                                                {{ $fest->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('association_type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Upload -->
                        <div class="mb-8">
                            <label for="images" class="siks-label mb-3">Select Images</label>
                            <div class="flex justify-center px-6 pt-8 pb-8 border-2 border-dashed border-gray-300 rounded-xl hover:border-siks-primary hover:bg-siks-primary/5 transition-all duration-200" id="drop-zone">
                                <div class="space-y-4 text-center">
                                    <div class="w-16 h-16 bg-siks-primary/10 rounded-full flex items-center justify-center mx-auto">
                                        <svg class="w-8 h-8 text-siks-primary" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div>
                                        <label for="images" class="cursor-pointer">
                                            <span class="siks-btn-primary inline-block">Choose Files</span>
                                            <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*" required>
                                        </label>
                                        <p class="mt-2 siks-body text-gray-600">or drag and drop images here</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="siks-body-small text-gray-500">
                                            PNG, JPG, GIF, WebP up to 5MB each
                                        </p>
                                        <p class="siks-body-small text-gray-500">
                                            Maximum 10 files at once
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @error('images')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('images.*')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview Area -->
                        <div id="preview-area" class="mb-8 hidden">
                            <h3 class="siks-heading-4 mb-6">Selected Images</h3>
                            <div id="image-previews" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                                <!-- Previews will be inserted here -->
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('gallery.index') }}" class="siks-btn-ghost w-full sm:w-auto">
                                Cancel
                            </a>
                            <button type="submit" class="siks-btn-primary w-full sm:w-auto" id="submit-btn">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload Images
                            </button>
                        </div>

                        <!-- Progress Bar -->
                        <div id="upload-progress" class="mt-6 hidden">
                            <div class="bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div id="progress-bar" class="bg-gradient-to-r from-siks-primary to-siks-light h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p class="siks-body-small text-gray-600 mt-2 text-center">Uploading images...</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('upload-form');
            const fileInput = document.getElementById('images');
            const dropZone = document.getElementById('drop-zone');
            const previewArea = document.getElementById('preview-area');
            const imagePreviews = document.getElementById('image-previews');
            const submitBtn = document.getElementById('submit-btn');
            const uploadProgress = document.getElementById('upload-progress');
            const progressBar = document.getElementById('progress-bar');
            
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
            
            let selectedFiles = [];
            
            // File input change handler
            fileInput.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });
            
            // Drag and drop handlers
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.classList.add('border-siks-primary', 'bg-siks-primary/10');
            });
            
            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-siks-primary', 'bg-siks-primary/10');
            });
            
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-siks-primary', 'bg-siks-primary/10');
                handleFiles(e.dataTransfer.files);
            });
            
            function handleFiles(files) {
                selectedFiles = Array.from(files).slice(0, 10); // Limit to 10 files
                
                if (selectedFiles.length === 0) {
                    previewArea.classList.add('hidden');
                    return;
                }
                
                // Clear previous previews
                imagePreviews.innerHTML = '';
                previewArea.classList.remove('hidden');
                
                selectedFiles.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'relative group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow';
                            previewDiv.innerHTML = `
                                <div class="aspect-square overflow-hidden bg-gray-100">
                                    <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover">
                                </div>
                                <button type="button" class="absolute top-3 right-3 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-all shadow-lg" onclick="removeFile(${index})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div class="p-4">
                                    <input type="text" name="captions[]" placeholder="Add a caption (optional)" class="w-full text-sm border-gray-300 rounded-lg focus:border-siks-primary focus:ring-siks-primary" maxlength="255">
                                    <p class="text-xs text-gray-500 mt-2 truncate" title="${file.name}">${file.name}</p>
                                    <p class="text-xs text-gray-400">${formatFileSize(file.size)}</p>
                                </div>
                            `;
                            imagePreviews.appendChild(previewDiv);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                // Update file input with selected files
                updateFileInput();
            }
            
            function updateFileInput() {
                const dt = new DataTransfer();
                selectedFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
            }
            
            window.removeFile = function(index) {
                selectedFiles.splice(index, 1);
                handleFiles(selectedFiles);
            };
            
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // Form submission with progress
            form.addEventListener('submit', function(e) {
                if (selectedFiles.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one image to upload.');
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Uploading...';
                uploadProgress.classList.remove('hidden');
                
                // Simulate progress (since we can't easily track real progress with standard form submission)
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    progressBar.style.width = progress + '%';
                }, 200);
                
                // Clear interval after form submission
                setTimeout(() => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';
                }, 1000);
            });
        });
    </script>
    @endpush
</x-page-layout>