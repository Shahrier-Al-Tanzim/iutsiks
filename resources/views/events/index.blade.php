{{-- resources/views/events/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Events') }}
        </h2>
    </x-slot>
    <div class="py-8 bg-green-900 min-h-screen">
        <div class="max-w-4xl mx-auto px-4">
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-800 text-green-100">
                    {{ session('success') }}
                </div>
            @endif
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl text-green-200">All Events</h3>
                @auth
                    <a href="{{ route('events.create') }}" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded shadow">+ New Event</a>
                @endauth
            </div>
            <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-gray-200">
                    <thead>
                        <tr class="bg-green-900">
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Time</th>
                            <th class="px-4 py-2 text-left">Author</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="px-4 py-2">{{ $event->title }}</td>
                                <td class="px-4 py-2">{{ $event->event_date }}</td>
                                <td class="px-4 py-2">{{ $event->event_time }}</td>
                                <td class="px-4 py-2">{{ $event->author->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-2 flex gap-2">
                                    <a href="{{ route('events.show', $event) }}" class="text-green-400 hover:underline">View</a>
                                    @auth
                                        @if($event->author_id === auth()->id())
                                            <a href="{{ route('events.edit', $event) }}" class="text-yellow-400 hover:underline">Edit</a>
                                            <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:underline" onclick="return confirm('Delete this event?')">Delete</button>
                                            </form>
                                        @endif
                                    @endauth
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400">No events found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
