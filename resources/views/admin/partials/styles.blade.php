{{-- Use Vite when built assets exist; otherwise Tailwind CDN so admin UI works without npm build. --}}
@php
    $viteReady = file_exists(public_path('build/manifest.json'));
@endphp
@if ($viteReady)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
@endif
