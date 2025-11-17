{{-- filepath: resources/views/blogs/index.blade.php --}}
@auth
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Blogs') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto px-4">
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-800 text-green-100">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl text-green-200">All Blogs</h3>
                <a href="{{ route('blogs.create') }}"
                   class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded shadow">
                    + New Blog
                </a>
            </div>

            <div class="bg-gray-800 rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-gray-200">
                    <thead>
                        <tr class="bg-green-900">
                            <th class="px-4 py-2 text-left">Title</th>
                            <th class="px-4 py-2 text-left">Content</th>
                            <th class="px-4 py-2 text-left">Image</th>
                            <th class="px-4 py-2 text-left">Author</th>
                            <th class="px-4 py-2 text-left">Created</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blogs as $blog)
                            <tr class="border-b border-gray-700 hover:bg-gray-700">
                                <td class="px-4 py-2">{{ $blog->title }}</td>
                                <td class="px-4 py-2">{{ $blog->content }}</td>
                                <td class="px-4 py-2">
                                    @if($blog->image)
                                        <img src="{{ asset('storage/' . $blog->image) }}" class="w-16 h-16 object-cover rounded">
                                    @else
                                        <span class="text-gray-500">No image</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $blog->author->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-2">{{ $blog->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-2 flex gap-2">
                                    <a href="{{ route('blogs.show', $blog) }}"
                                       class="text-green-400 hover:underline">View</a>
                                    @if($blog->author_id === auth()->id())
                                        <a href="{{ route('blogs.edit', $blog) }}"
                                           class="text-yellow-400 hover:underline">Edit</a>
                                        <form action="{{ route('blogs.destroy', $blog) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-400 hover:underline"
                                                onclick="return confirm('Delete this blog?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400">No blogs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $blogs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
@else
<div class="flex items-center justify-center min-h-screen bg-gray-900">
    <a href="{{ route('login') }}" class="bg-green-700 hover:bg-green-600 text-white px-6 py-4 rounded shadow text-xl font-bold">
        PLEASE LOG IN TO CONTINUE
    </a>
</div>
@endauth
