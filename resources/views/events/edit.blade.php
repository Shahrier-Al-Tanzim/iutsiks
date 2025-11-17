{{-- resources/views/events/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Edit Event') }}
        </h2>
    </x-slot>
    <div class="py-8 bg-green-900 min-h-screen">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-gray-800 rounded-lg shadow p-8">
                @if ($errors->any())
                    <div class="mb-4 text-red-400">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('events.update', $event) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="title">Title</label>
                        <input id="title" name="title" type="text" class="w-full rounded border-gray-600 bg-gray-900 text-green-100 focus:ring-green-400 focus:border-green-400" value="{{ old('title', $event->title) }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full rounded border-gray-600 bg-gray-900 text-green-100 focus:ring-green-400 focus:border-green-400">{{ old('description', $event->description) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="event_date">Date</label>
                        <input id="event_date" name="event_date" type="date" class="w-full rounded border-gray-600 bg-gray-900 text-green-100 focus:ring-green-400 focus:border-green-400" value="{{ old('event_date', $event->event_date) }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="event_time">Time</label>
                        <input id="event_time" name="event_time" type="time" class="w-full rounded border-gray-600 bg-gray-900 text-green-100 focus:ring-green-400 focus:border-green-400" value="{{ old('event_time', $event->event_time) }}" required>
                    </div>
                    <div class="flex justify-end">
                        <a href="{{ route('events.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">Cancel</a>
                        <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
