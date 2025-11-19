<x-page-layout>
    <x-slot name="title">{{ $blog->title }} - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">{{ $blog->title }}</h1>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-white/90">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    By {{ $blog->author->name ?? 'Unknown' }}
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $blog->created_at->format('F j, Y') }}
                </div>
            </div>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-4xl mx-auto">
            <div class="siks-card p-8">
                <!-- Blog Image -->
                @if ($blog->image)
                    <div class="mb-8">
                        <img src="{{ asset('storage/' . $blog->image) }}" 
                             class="w-full h-64 object-cover rounded-lg" 
                             alt="{{ $blog->title }}">
                    </div>
                @endif

                <!-- Blog Content -->
                <div class="prose prose-lg max-w-none">
                    <div class="siks-body text-gray-700 whitespace-pre-line leading-relaxed">
                        {{ $blog->content }}
                    </div>
                </div>

                <!-- Blog Meta -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            Written by {{ $blog->author->name ?? 'Unknown' }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Published on {{ $blog->created_at->format('F j, Y \a\t g:i A') }}
                        </div>
                        @if($blog->updated_at != $blog->created_at)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                                Last updated {{ $blog->updated_at->format('F j, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-4 mt-8">
                <a href="{{ route('blogs.index') }}" class="siks-btn-ghost">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Blogs
                </a>
                
                @auth
                    @if($blog->author_id === auth()->id() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('blogs.edit', $blog) }}" class="siks-btn-outline">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Blog
                        </a>
                        <form action="{{ route('blogs.destroy', $blog) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this blog post? This action cannot be undone.')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Blog
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </x-section>
</x-page-layout>
