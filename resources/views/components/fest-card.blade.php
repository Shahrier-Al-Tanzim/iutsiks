@props(['fest'])

<div class="siks-card siks-card-hover">
    @if($fest->banner_image)
        <div class="aspect-w-16 aspect-h-9">
            <img src="{{ asset('storage/' . $fest->banner_image) }}" 
                 alt="{{ $fest->title }}" 
                 class="w-full h-48 object-cover">
        </div>
    @endif
    
    <div class="p-6">
        <div class="flex justify-between items-start mb-2">
            <h3 class="siks-heading-4 line-clamp-2">
                <a href="{{ route('fests.show', $fest) }}" class="hover:text-siks-darker transition-colors">
                    {{ $fest->title }}
                </a>
            </h3>
            
            @auth
                @if(auth()->user()->canManageFests())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($fest->status === 'published') bg-green-100 text-green-800
                        @elseif($fest->status === 'draft') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($fest->status) }}
                    </span>
                @endif
            @endauth
        </div>
        
        <p class="siks-body-small text-gray-600 mb-4 line-clamp-3">
            {{ Str::limit($fest->description, 120) }}
        </p>
        
        <div class="flex items-center justify-between siks-body-small text-gray-500 mb-4">
            <div>
                <span class="font-medium">{{ $fest->start_date->format('M j') }}</span>
                @if($fest->start_date->format('Y-m-d') !== $fest->end_date->format('Y-m-d'))
                    - <span class="font-medium">{{ $fest->end_date->format('M j, Y') }}</span>
                @else
                    <span>, {{ $fest->start_date->format('Y') }}</span>
                @endif
            </div>
            
            <div class="text-right">
                <span class="font-medium">{{ $fest->events_count ?? $fest->events->count() }}</span>
                <span>{{ Str::plural('event', $fest->events_count ?? $fest->events->count()) }}</span>
            </div>
        </div>
        
        <div class="flex justify-between items-center">
            <a href="{{ route('fests.show', $fest) }}" 
               class="siks-btn-primary">
                View Details
            </a>
            
            @auth
                @if(auth()->user()->canManageFests())
                    <div class="flex space-x-2">
                        <a href="{{ route('fests.edit', $fest) }}" 
                           class="siks-btn-base bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500">
                            Edit
                        </a>
                        <form action="{{ route('fests.destroy', $fest) }}" 
                              method="POST" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this fest?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="siks-btn-base bg-red-500 text-white hover:bg-red-600 focus:ring-red-500">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>