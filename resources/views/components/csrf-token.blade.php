{{-- CSRF Token Meta Tag --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- JavaScript to set CSRF token for AJAX requests --}}
<script>
    // Set CSRF token for all AJAX requests
    window.Laravel = {
        csrfToken: '{{ csrf_token() }}'
    };
    
    // Set default CSRF token for axios if available
    if (typeof axios !== 'undefined') {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
    
    // Set default CSRF token for jQuery AJAX if available
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }
</script>