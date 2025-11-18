<x-page-layout>
    <x-slot name="title">Create Blog - SIKS</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Create Blog Post</h1>
            <p class="siks-body text-white/90">Share your thoughts and insights with the SIKS community</p>
        </div>
    </x-section>

    <!-- Form Section -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="siks-heading-3">Blog Details</h2>
                    <a href="{{ route('blogs.index') }}" class="siks-btn-ghost">
                        Back to Blogs
                    </a>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('blogs.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="siks-label">Blog Title</label>
                        <input 
                            id="title" 
                            name="title" 
                            type="text" 
                            class="siks-input @error('title') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                            value="{{ old('title') }}" 
                            placeholder="Enter an engaging title for your blog post"
                            required
                        >
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="siks-label">Content</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="12" 
                            class="siks-textarea @error('content') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                            placeholder="Write your blog content here. You can use markdown formatting..."
                            required
                        >{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            You can use basic markdown formatting like **bold**, *italic*, and [links](url).
                        </p>
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label for="image" class="siks-label">Featured Image (Optional)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-siks-primary hover:bg-siks-primary/5 transition-all duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-siks-primary hover:text-siks-darker focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-siks-primary">
                                        <span>Upload a file</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                            </div>
                        </div>
                        @error('image')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('blogs.index') }}" class="siks-btn-ghost w-full sm:w-auto">
                            Cancel
                        </a>
                        <button type="submit" class="siks-btn-primary w-full sm:w-auto">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Blog Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File upload preview
            const fileInput = document.getElementById('image');
            const dropZone = fileInput.closest('.border-dashed');
            
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const fileName = file.name;
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    
                    // Update the drop zone to show selected file
                    dropZone.innerHTML = `
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-siks-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div class="text-sm text-gray-900">
                                <p class="font-medium">${fileName}</p>
                                <p class="text-gray-500">${fileSize} MB</p>
                            </div>
                            <button type="button" onclick="clearFile()" class="text-sm text-siks-primary hover:text-siks-darker">
                                Remove file
                            </button>
                        </div>
                    `;
                }
            });
            
            // Clear file function
            window.clearFile = function() {
                fileInput.value = '';
                location.reload(); // Simple way to reset the drop zone
            };
            
            // Drag and drop functionality
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
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
    @endpush
</x-page-layout>
