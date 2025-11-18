{{-- resources/views/events/create.blade.php --}}
<x-page-layout>
    <x-slot name="title">Create Event - SIKS</x-slot>
    
    <!-- Header Section -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Create Event</h1>
            <p class="siks-body text-white/90">Add a new event to the SIKS calendar</p>
        </div>
    </x-section>

    <!-- Form Section -->
    <x-section>
        <div class="max-w-2xl mx-auto">
            <div class="siks-card p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <h4 class="text-red-800 font-medium mb-2">Please fix the following errors:</h4>
                        <ul class="text-red-700 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Fest Association -->
                    <div class="mb-6">
                        <label class="siks-label" for="fest_id">Associated Fest (Optional)</label>
                        <select id="fest_id" name="fest_id" class="siks-select">
                            <option value="">Select a fest (optional)</option>
                            @foreach($fests as $fest)
                                <option value="{{ $fest->id }}" {{ old('fest_id') == $fest->id ? 'selected' : '' }}>
                                    {{ $fest->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Basic Information -->
                    <div class="mb-6">
                        <label class="siks-label" for="title">Title</label>
                        <input id="title" name="title" type="text" class="siks-input" value="{{ old('title') }}" required>
                    </div>
                    
                    <div class="mb-6">
                        <label class="siks-label" for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="siks-textarea" required>{{ old('description') }}</textarea>
                    </div>

                    <!-- Event Type -->
                    <div class="mb-6">
                        <label class="siks-label" for="type">Event Type</label>
                        <select id="type" name="type" class="siks-select" required>
                            <option value="">Select event type</option>
                            <option value="quiz" {{ old('type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                            <option value="lecture" {{ old('type') == 'lecture' ? 'selected' : '' }}>Lecture</option>
                            <option value="donation" {{ old('type') == 'donation' ? 'selected' : '' }}>Donation</option>
                            <option value="competition" {{ old('type') == 'competition' ? 'selected' : '' }}>Competition</option>
                            <option value="workshop" {{ old('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                        </select>
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="siks-label" for="event_date">Date</label>
                            <input id="event_date" name="event_date" type="date" class="siks-input" value="{{ old('event_date') }}" required>
                        </div>
                        <div>
                            <label class="siks-label" for="event_time">Time</label>
                            <input id="event_time" name="event_time" type="time" class="siks-input" value="{{ old('event_time') }}" required>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mb-6">
                        <label class="siks-label" for="location">Location</label>
                        <input id="location" name="location" type="text" class="siks-input" value="{{ old('location') }}" placeholder="e.g., Main Auditorium, Room 101">
                    </div>

                    <!-- Registration Settings -->
                    <div class="mb-6">
                        <label class="siks-label" for="registration_type">Registration Type</label>
                        <select id="registration_type" name="registration_type" class="siks-select" required>
                            <option value="">Select registration type</option>
                            <option value="individual" {{ old('registration_type') == 'individual' ? 'selected' : '' }}>Individual Only</option>
                            <option value="team" {{ old('registration_type') == 'team' ? 'selected' : '' }}>Team Only</option>
                            <option value="both" {{ old('registration_type') == 'both' ? 'selected' : '' }}>Both Individual & Team</option>
                            <option value="on_spot" {{ old('registration_type') == 'on_spot' ? 'selected' : '' }}>On-Spot Registration</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="siks-label" for="max_participants">Max Participants (Optional)</label>
                            <input id="max_participants" name="max_participants" type="number" min="1" class="siks-input" value="{{ old('max_participants') }}" placeholder="Leave empty for unlimited">
                        </div>
                        <div>
                            <label class="siks-label" for="fee_amount">Registration Fee (BDT)</label>
                            <input id="fee_amount" name="fee_amount" type="number" min="0" step="0.01" class="siks-input" value="{{ old('fee_amount', 0) }}" placeholder="0 for free">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="siks-label" for="registration_deadline">Registration Deadline (Optional)</label>
                        <input id="registration_deadline" name="registration_deadline" type="datetime-local" class="siks-input" value="{{ old('registration_deadline') }}">
                        <p class="siks-body-small text-gray-600 mt-1">Leave empty if no deadline</p>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label class="siks-label" for="status">Status</label>
                        <select id="status" name="status" class="siks-select" required>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-6">
                        <label class="siks-label" for="image">Upload Image</label>
                        <input id="image" name="image" type="file" class="siks-input" accept="image/*">
                        @if ($errors->has('image'))
                            <p class="siks-error">{{ $errors->first('image') }}</p>
                        @endif
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('events.index') }}" class="siks-btn-ghost">Cancel</a>
                        <button type="submit" class="siks-btn-primary">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </x-section>
</x-page-layout>
