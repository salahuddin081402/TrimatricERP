{{-- resources/views/backend/layouts/master.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    @include('backend.layouts.partials.head')
    {{-- IMPORTANT: sticky-header CSS --}}
    <style>
        /* Make HEADER sticky (sits above everything) */
        header {
            position: sticky;
            top: 0;
            z-index: 2000; /* above sidebar (1050), dropdowns, sliders */
            background: inherit; /* keeps your gray theme */
        }

        /* Prevent content from sliding under sticky header */
        .content-wrapper {
            padding-top: 20px !important;
        }

        /* Keep sidebar from overlapping header on mobile */
        .main-sidebar {
            z-index: 1500 !important;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- Sticky Header --}}
    @include('backend.layouts.partials.header')

    {{-- Sidebar (unchanged) --}}
    @include('backend.layouts.partials.sidebar')

    {{-- Main Content --}}
    <main class="content-wrapper">
        @include('backend.layouts.partials.alerts')
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('backend.layouts.partials.footer')

</div>

{{-- Global Image Zoom Modal --}}
@include('backend.layouts.partials.zoom-modal')

@include('backend.layouts.partials.scripts')
</body>
</html>
