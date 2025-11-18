<x-page-layout>
    <x-slot name="title">Blogs - SIKS</x-slot>
    
    <!-- Page Header -->
    <x-section background="primary" padding="medium">
        <div class="text-center">
            <h1 class="siks-heading-1 text-white mb-4">Our Blog</h1>
            <p class="siks-body text-white/90 max-w-2xl mx-auto">
                Stay updated with the latest articles, insights, and Islamic content from our community.
            </p>
        </div>
    </x-section>

    <!-- Main Content -->
    <x-section>
        <div class="max-w-6xl mx-auto">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="siks-heading-2 mb-2">Latest Articles</h2>
                    <p class="siks-body text-gray-600">
                        {{ $blogs->total() }} {{ Str::plural('article', $blogs->total()) }} published
                    </p>
                </div>
                @can('create', App\Models\Blog::class)
                    <a href="{{ route('blogs.create') }}" class="siks-btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Write New Article
                    </a>
                @endcan
            </div>

            <!-- Blog Grid -->
            @if($blogs->count() > 0)
                <div class="siks-grid-3 mb-8">
                    @foreach($blogs as $blog)
                        <x-blog-card :blog="$blog" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="flex justify-center">
                    {{ $blogs->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <h3 class="siks-heading-3 mb-4">No Articles Yet</h3>
                    <p class="siks-body text-gray-600 mb-8 max-w-md mx-auto">
                        We haven't published any articles yet. Check back soon for inspiring content and Islamic insights.
                    </p>
                    @can('create', App\Models\Blog::class)
                        <a href="{{ route('blogs.create') }}" class="siks-btn-primary">
                            Write the First Article
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </x-section>
</x-page-layout>
