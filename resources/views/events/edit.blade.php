<x-page-layout>
    <x-slot name="title">Edit Event - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Edit Event</h1>
            <p class="siks-body text-white/90">
                Update event details and settings
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
                <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Fest Association -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="fest_id">Associated Fest (Optional)</label>
                        <select id="fest_id" name="fest_id" class="siks-select">
                            <option value="">Select a fest (optional)</option>
                            @foreach($fests as $fest)
                                <option value="{{ $fest->id }}" {{ old('fest_id', $event->fest_id) == $fest->id ? 'selected' : '' }}>
                                    {{ $fest->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Basic Information -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="title">Title</label>
                        <input id="title" name="title" type="text" class="siks-input" value="{{ old('title', $event->title) }}" required>
                        @error('title')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="siks-form-group">
                        <label class="siks-label" for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="siks-textarea" required>{{ old('description', $event->description) }}</textarea>
                        @error('description')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Event Type -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="type">Event Type</label>
                        <select id="type" name="type" class="siks-select" required>
                            <option value="">Select event type</option>
                            <option value="quiz" {{ old('type', $event->type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                            <option value="lecture" {{ old('type', $event->type) == 'lecture' ? 'selected' : '' }}>Lecture</option>
                            <option value="donation" {{ old('type', $event->type) == 'donation' ? 'selected' : '' }}>Donation</option>
                            <option value="competition" {{ old('type', $event->type) == 'competition' ? 'selected' : '' }}>Competition</option>
                            <option value="workshop" {{ old('type', $event->type) == 'workshop' ? 'selected' : '' }}>Workshop</option>
                        </select>
                    </div>

                    <!-- Date and Time -->
                    <div class="siks-form-row">
                        <div class="siks-form-group">
                            <label class="siks-label" for="event_date">Date</label>
                            <input id="event_date" name="event_date" type="date" class="siks-input" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required>
                            @error('event_date')
                                <p class="siks-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="siks-form-group">
                            <label class="siks-label" for="event_time">Time</label>
                            <input id="event_time" name="event_time" type="time" class="siks-input" value="{{ old('event_time', $event->event_time ? $event->event_time->format('H:i') : '') }}" required>
                            @error('event_time')
                                <p class="siks-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="location">Location</label>
                        <input id="location" name="location" type="text" class="siks-input" value="{{ old('location', $event->location) }}" placeholder="e.g., Main Auditorium, Room 101">
                        @error('location')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Registration Settings -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="registration_type">Registration Type</label>
                        <select id="registration_type" name="registration_type" class="siks-select" required>
                            <option value="">Select registration type</option>
                            <option value="individual" {{ old('registration_type', $event->registration_type) == 'individual' ? 'selected' : '' }}>Individual Only</option>
                            <option value="team" {{ old('registration_type', $event->registration_type) == 'team' ? 'selected' : '' }}>Team Only</option>
                            <option value="both" {{ old('registration_type', $event->registration_type) == 'both' ? 'selected' : '' }}>Both Individual & Team</option>
                            <option value="on_spot" {{ old('registration_type', $event->registration_type) == 'on_spot' ? 'selected' : '' }}>On-Spot Registration</option>
                        </select>
                    </div>

                    <div class="siks-form-row">
                        <div class="siks-form-group">
                            <label class="siks-label" for="max_participants">Max Participants (Optional)</label>
                            <input id="max_participants" name="max_participants" type="number" min="1" class="siks-input" value="{{ old('max_participants', $event->max_participants) }}" placeholder="Leave empty for unlimited">
                            @error('max_participants')
                                <p class="siks-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="siks-form-group">
                            <label class="siks-label" for="fee_amount">Registration Fee (BDT)</label>
                            <input id="fee_amount" name="fee_amount" type="number" min="0" step="0.01" class="siks-input" value="{{ old('fee_amount', $event->fee_amount) }}" placeholder="0 for free">
                            @error('fee_amount')
                                <p class="siks-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="siks-form-group">
                        <label class="siks-label" for="registration_deadline">Registration Deadline (Optional)</label>
                        <input id="registration_deadline" name="registration_deadline" type="datetime-local" class="siks-input" value="{{ old('registration_deadline', $event->registration_deadline ? $event->registration_deadline->format('Y-m-d\TH:i') : '') }}">
                        <p class="siks-body-small text-gray-500 mt-1">Leave empty if no deadline</p>
                        @error('registration_deadline')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="status">Status</label>
                        <select id="status" name="status" class="siks-select" required>
                            <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $event->status) == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="siks-form-group">
                        <label class="siks-label" for="image">Upload Image</label>
                        @if($event->image)
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . $event->image) }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                            </div>
                        @endif
                        <input id="image" name="image" type="file" class="siks-file-input" accept="image/*">
                        <p class="siks-body-small text-gray-500 mt-1">Accepted formats: JPG, PNG, GIF. Max size: 2MB</p>
                        @error('image')
                            <p class="siks-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Form Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('events.index') }}" class="siks-btn-ghost">
                            Cancel
                        </a>
                        <button type="submit" class="siks-btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-section>
</x-page-layout>
