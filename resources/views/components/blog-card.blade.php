@props(['blog', 'showAuthor' => true, 'showExcerpt' => true])

<article class="siks-card siks-card-hover">
    <!-- Blog Image -->
    @if($blog->image)
        <div class="h-48 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $blog->image) }}')"></div>
    @else
        <div class="h-48 bg-gradient-to-br from-siks-primary to-siks-dark flex items-center justify-center">
            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
        </div>
    @endif

    <div class="p-6">
        <!-- Blog Header -->
        <div class="mb-4">
            <h3 class="siks-heading-4 mb-2">
                <a href="{{ route('blogs.show', $blog) }}" class="hover:text-siks-primary transition-colors">
                    {{ $blog->title }}
                </a>
            </h3>
            
            <div class="flex items-center gap-4 siks-body-small text-gray-500">
                @if($showAuthor)
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $blog->author->name ?? 'Unknown' }}
                    </span>
                @endif
                
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $blog->created_at->format('M j, Y') }}
                </span>
            </div>
        </div>

        <!-- Blog Excerpt -->
        @if($showExcerpt)
            <div class="mb-4">
                <p class="siks-body text-gray-600 line-clamp-3">
                    {{ Str::limit(strip_tags($blog->content), 150) }}
                </p>
            </div>
        @endif

        <!-- Blog Stats -->
        <div class="mb-4 flex items-center gap-4 siks-body-small text-gray-500">
            @if(method_exists($blog, 'comments'))
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                    </svg>
                    {{ $blog->comments->count() }} comments
                </span>
            @endif
            
            @if(method_exists($blog, 'likes'))
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                    </svg>
                    {{ $blog->likes->count() }} likes
                </span>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('blogs.show', $blog) }}" class="siks-btn-primary flex-1 text-center">
                Read More
            </a>
            
            @auth
                @can('update', $blog)
                    <a href="{{ route('blogs.edit', $blog) }}" class="siks-btn-base bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500">
                        Edit
                    </a>
                @endcan
                
                @can('delete', $blog)
                    <form action="{{ route('blogs.destroy', $blog) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="siks-btn-base bg-red-500 text-white hover:bg-red-600 focus:ring-red-500" onclick="return confirm('Delete this blog post?')">
                            Delete
                        </button>
                    </form>
                @endcan
            @endauth
        </div>
    </div>
</article>