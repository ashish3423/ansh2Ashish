
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard')</title>

    <link rel="stylesheet"
          href="{{ asset('vendors/simplebar/css/simplebar.css') }}">

    <link rel="stylesheet"
          href="{{ asset('css/vendors/simplebar.css') }}">

    <link rel="stylesheet"
          href="{{ asset('css/style.css') }}">

    <link rel="stylesheet"
          href="{{ asset('css/examples.css') }}">

    <link rel="stylesheet"
          href="{{ asset('vendors/@coreui/chartjs/css/coreui-chartjs.css') }}">

    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/color-modes.js') }}"></script>
</head>

<body>
    @include('layouts.sidebar')

    <div class="wrapper d-flex flex-column min-vh-100">

        @include('layouts.header')

        <div class="body flex-grow-1">
            <div class="container-lg px-4">
                @yield('content')
            </div>
        </div>

        @include('layouts.footer')

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('vendors/@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('vendors/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('vendors/chart.js/js/chart.umd.js') }}"></script>
    <script src="{{ asset('vendors/@coreui/chartjs/js/coreui-chartjs.js') }}"></script>
    <script src="{{ asset('vendors/@coreui/utils/js/index.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>




</body>
</html>
