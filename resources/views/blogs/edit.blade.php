<x-page-layout>
    <x-slot name="title">Edit Blog - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Edit Blog</h1>
            <p class="siks-body text-white/90">
                Update your blog post content and settings
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                @if ($errors->any())
                    <div class="siks-card p-4 mb-6 bg-red-50 border border-red-200">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h3 class="text-red-800 font-medium mb-2">Please fix the following errors:</h3>
                                <ul class="text-red-700 text-sm space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ route('blogs.update', $blog) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Title -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="title">Title</label>
                        <input id="title" name="title" type="text" class="siks-input" value="{{ old('title', $blog->title) }}" required>
                        @error('title')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Content -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="content">Content</label>
                        <textarea id="content" name="content" rows="12" class="siks-textarea" required>{{ old('content', $blog->content) }}</textarea>
                        @error('content')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Current Image -->
                    @if ($blog->image)
                        <div class="siks-form-group">
                            <label class="siks-label">Current Image</label>
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $blog->image) }}" class="w-48 h-32 object-cover rounded-lg border border-gray-200">
                            </div>
                        </div>
                    @endif

                    <!-- Image Upload -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="image">
                            @if($blog->image)
                                Change Image (Optional)
                            @else
                                Upload Image (Optional)
                            @endif
                        </label>
                        <input id="image" name="image" type="file" class="siks-file-input" accept="image/*">
                        <p class="siks-body-small text-gray-500 mt-1">
                            @if($blog->image)
                                Leave empty to keep current image. 
                            @endif
                            Accepted formats: JPG, PNG, GIF. Max size: 2MB
                        </p>
                        @error('image')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('blogs.index') }}" class="siks-btn-ghost">
                            Cancel
                        </a>
                        <button type="submit" class="siks-btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Blog
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-section>
</x-page-layout>
