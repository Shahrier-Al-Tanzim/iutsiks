{{-- resources/views/blogs/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-green-400 dark:text-green-300 leading-tight">
            {{ __('Edit Blog') }}
        </h2>
    </x-slot>
    <div class="py-8 bg-gray-900 min-h-screen">
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
                <form method="POST" action="{{ route('blogs.update', $blog) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="title">Title</label>
                        <input id="title" name="title" type="text" class="w-full rounded border-gray-600 bg-gray-900 text-green-100 focus:ring-green-400 focus:border-green-400" value="{{ old('title', $blog->title) }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="content">Content</label>
                        <textarea id="content" name="content" rows="6" class="w-full rounded border-gray-600 bg-gray-900 text-green-100 focus:ring-green-400 focus:border-green-400" required>{{ old('content', $blog->content) }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <a href="{{ route('blogs.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">Cancel</a>
                        <button type="submit" class="bg-green-700 hover:bg-green-600 text-white px-4 py-2 rounded">Update</button>
                    </div>
                    <!-- image preview if already exists -->
                    @if ($blog->image)
                        <img src="{{ asset('storage/' . $blog->image) }}" class="w-32 h-32 object-cover mb-4 rounded">
                    @endif

                    <div class="mb-4">
                        <label class="block text-green-200 mb-2" for="image">Change Image</label>
                        <input id="image" name="image" type="file" class="text-white">
                    </div>
                </form>
                {{-- <form method="POST" action="{{ route('blogs.update', $blog) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- title, content fields -->
                </form> --}}
            </div>
        </div>
    </div>
</x-app-layout>
