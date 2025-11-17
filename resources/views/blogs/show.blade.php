{{-- resources/views/blogs/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Blog Details') }}
        </h2>
    </x-slot>
    <div class="py-8 bg-gray-900 min-h-screen">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-gray-800 rounded-lg shadow p-8">
                <h3 class="text-2xl text-green-200 mb-4">{{ $blog->title }}</h3>
                <div class="mb-4 text-gray-300">By {{ $blog->author->name ?? 'Unknown' }} on {{ $blog->created_at->format('Y-m-d H:i') }}</div>
                <div class="mb-8 text-gray-100 whitespace-pre-line">{{ $blog->content }}</div>
                @if ($blog->image)
                    <div class="mb-6">
                        <img src="{{ asset('storage/' . $blog->image) }}" class="max-w-xs max-h-48 mx-auto object-cover rounded">
                    </div>
                @endif
                <div class="flex gap-2">
                    <a href="{{ route('blogs.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">Back</a>
                    @auth
                        @if($blog->author_id === auth()->id())
                            <a href="{{ route('blogs.edit', $blog) }}" class="bg-yellow-700 hover:bg-yellow-600 text-white px-4 py-2 rounded">Edit</a>
                            <form action="{{ route('blogs.destroy', $blog) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('Delete this blog?')">Delete</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
