{{-- resources/views/events/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Event Details') }}
        </h2>
    </x-slot>
    <div class="py-8 bg-green-900 min-h-screen">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-gray-800 rounded-lg shadow p-8">
                <h3 class="text-2xl text-green-200 mb-4">{{ $event->title }}</h3>
                <div class="mb-2 text-gray-300">By {{ $event->author->name ?? 'Unknown' }} on {{ $event->event_date }} at {{ $event->event_time }}</div>
                <div class="mb-8 text-gray-100 whitespace-pre-line">{{ $event->description }}</div>
                <div class="flex gap-2">
                    <a href="{{ route('events.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">Back</a>
                    @auth
                        @if($event->author_id === auth()->id())
                            <a href="{{ route('events.edit', $event) }}" class="bg-yellow-700 hover:bg-yellow-600 text-white px-4 py-2 rounded">Edit</a>
                            <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('Delete this event?')">Delete</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
