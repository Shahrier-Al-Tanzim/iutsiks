<x-page-layout>
    <x-slot name="title">Create New Fest - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Create New Fest</h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                Create a new festival or multi-day event for the community.
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="siks-heading-2 mb-2">Fest Details</h2>
                    <p class="siks-body text-gray-600">Fill in the information for your new fest</p>
                </div>
                <a href="{{ route('fests.index') }}" class="siks-btn-ghost">
                    ← Back to Fests
                </a>
            </div>

            <div class="siks-card">
                <div class="p-6 sm:p-8">
                    <form action="{{ route('fests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Fest Title')" />
                            <x-text-input id="title" 
                                        name="title" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                        :value="old('title')" 
                                        required 
                                        maxlength="255" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" 
                                    name="description" 
                                    rows="6" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                    required 
                                    minlength="50">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Minimum 50 characters required</p>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" 
                                            name="start_date" 
                                            type="date" 
                                            class="mt-1 block w-full" 
                                            :value="old('start_date')" 
                                            required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" />
                                <x-text-input id="end_date" 
                                            name="end_date" 
                                            type="date" 
                                            class="mt-1 block w-full" 
                                            :value="old('end_date')" 
                                            required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Banner Image -->
                        <div>
                            <x-input-label for="banner_image" :value="__('Banner Image (Optional)')" />
                            <input id="banner_image" 
                                   name="banner_image" 
                                   type="file" 
                                   accept="image/jpeg,image/jpg,image/png"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                            <x-input-error :messages="$errors->get('banner_image')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Accepted formats: JPG, JPEG, PNG. Max size: 2MB</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" 
                                    name="status" 
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                    required>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Draft fests are only visible to admins</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('fests.index') }}" class="siks-btn-ghost">
                                Cancel
                            </a>
                            <button type="submit" class="siks-btn-primary">
                                {{ __('Create Fest') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-section>

    @push('scripts')
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
    @endpush
</x-page-layout>