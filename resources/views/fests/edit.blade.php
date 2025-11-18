<x-page-layout>
    <x-slot name="title">Edit Fest: {{ $fest->title }} - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Edit Fest</h1>
            <p class="siks-body text-white/90">
                Update fest details and settings
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                <form action="{{ route('fests.update', $fest) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="title">Fest Title</label>
                        <input id="title" 
                               name="title" 
                               type="text" 
                               class="siks-input" 
                               value="{{ old('title', $fest->title) }}" 
                               required 
                               maxlength="255">
                        @error('title')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="description">Description</label>
                        <textarea id="description" 
                                name="description" 
                                rows="6" 
                                class="siks-textarea" 
                                required 
                                minlength="50">{{ old('description', $fest->description) }}</textarea>
                        @error('description')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                        <p class="siks-body-small text-gray-500 mt-1">Minimum 50 characters required</p>
                    </div>

                    <!-- Date Range -->
                    <div class="siks-form-row">
                        <div class="siks-form-group">
                            <label class="siks-label" for="start_date">Start Date</label>
                            <input id="start_date" 
                                   name="start_date" 
                                   type="date" 
                                   class="siks-input" 
                                   value="{{ old('start_date', $fest->start_date->format('Y-m-d')) }}" 
                                   required>
                            @error('start_date')
                                <p class="siks-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="siks-form-group">
                            <label class="siks-label" for="end_date">End Date</label>
                            <input id="end_date" 
                                   name="end_date" 
                                   type="date" 
                                   class="siks-input" 
                                   value="{{ old('end_date', $fest->end_date->format('Y-m-d')) }}" 
                                   required>
                            @error('end_date')
                                <p class="siks-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Current Banner Image -->
                    @if($fest->banner_image)
                        <div class="siks-form-group">
                            <label class="siks-label">Current Banner Image</label>
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $fest->banner_image) }}" 
                                     alt="{{ $fest->title }}" 
                                     class="w-full max-w-md h-32 object-cover rounded-lg border border-gray-200">
                            </div>
                        </div>
                    @endif

                    <!-- Banner Image -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="banner_image">Banner Image (Optional)</label>
                        <input id="banner_image" 
                               name="banner_image" 
                               type="file" 
                               accept="image/jpeg,image/jpg,image/png"
                               class="siks-file-input">
                        @error('banner_image')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                        <p class="siks-body-small text-gray-500 mt-1">
                            @if($fest->banner_image)
                                Leave empty to keep current image. 
                            @endif
                            Accepted formats: JPG, JPEG, PNG. Max size: 2MB
                        </p>
                    </div>

                    <!-- Status -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="status">Status</label>
                        <select id="status" 
                                name="status" 
                                class="siks-select" 
                                required>
                            <option value="draft" {{ old('status', $fest->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $fest->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="completed" {{ old('status', $fest->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                        <p class="siks-body-small text-gray-500 mt-1">Draft fests are only visible to admins</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('fests.show', $fest) }}" class="siks-btn-ghost">
                            Cancel
                        </a>
                        <button type="submit" class="siks-btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Fest
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-section>

    <script>
        // Ensure end date is not before start date
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');
            endDateInput.min = startDate;
            
            if (endDateInput.value && endDateInput.value < startDate) {
                endDateInput.value = startDate;
            }
        });
    </script>
</x-app-layout>