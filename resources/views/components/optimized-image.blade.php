@props([
    'src',
    'alt' => '',
    'class' => '',
    'lazy' => true,
    'responsive' => false,
    'srcset' => null,
    'sizes' => '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw',
    'placeholder' => null
])

@php
    $imageClasses = 'transition-opacity duration-300 ' . $class;
    if ($lazy) {
        $imageClasses .= ' lazy-load opacity-0';
    }
@endphp

<div class="relative overflow-hidden">
    @if($placeholder && $lazy)
        <!-- Placeholder for lazy loading -->
        <div class="absolute inset-0 bg-gray-200 animate-pulse lazy-placeholder"></div>
    @endif
    
    <img 
        @if($lazy) 
            data-src="{{ $src }}"
            @if($responsive && $srcset)
                data-srcset="{{ $srcset }}"
                data-sizes="{{ $sizes }}"
            @endif
        @else
            src="{{ $src }}"
            @if($responsive && $srcset)
                srcset="{{ $srcset }}"
                sizes="{{ $sizes }}"
            @endif
        @endif
        alt="{{ $alt }}"
        class="{{ $imageClasses }}"
        {{ $attributes }}
        @if($lazy)
            loading="lazy"
        @endif
    />
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const placeholder = img.parentElement.querySelector('.lazy-placeholder');
                    
                    // Load the image
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                        img.removeAttribute('data-srcset');
                    }
                    
                    if (img.dataset.sizes) {
                        img.sizes = img.dataset.sizes;
                        img.removeAttribute('data-sizes');
                    }
                    
                    // Handle load event
                    img.onload = function() {
                        img.classList.remove('opacity-0');
                        img.classList.add('opacity-100');
                        if (placeholder) {
                            placeholder.remove();
                        }
                    };
                    
                    // Handle error event
                    img.onerror = function() {
                        img.src = '/images/placeholder.jpg';
                        img.classList.remove('opacity-0');
                        img.classList.add('opacity-100');
                        if (placeholder) {
                            placeholder.remove();
                        }
                    };
                    
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        // Observe all lazy load images
        document.querySelectorAll('.lazy-load').forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without Intersection Observer
        document.querySelectorAll('.lazy-load').forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
                img.removeAttribute('data-srcset');
            }
            img.classList.remove('opacity-0');
            img.classList.add('opacity-100');
        });
    }
});
</script>
@endpush
@endonce