<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-coreui-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account') · Ansh Enterprises</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="stylesheet" href="{{ asset('vendors/simplebar/css/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/examples.min.css') }}">
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/color-modes.js') }}"></script>
</head>
<body>
<main class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center py-4">
    <div class="container" style="max-width: 32rem">
        <div class="d-flex flex-column gap-4 @yield('stack-class')">
            <!-- <a class="text-primary text-decoration-none text-center fs-2 fw-semibold" href="{{ route('login') }}" aria-label="CoreUI home">
                <svg role="img" aria-label="CoreUI" height="48" viewBox="0 0 312 115" xmlns="http://www.w3.org/2000/svg"><g fill="currentColor"><path d="M96 24 57 2a12 12 0 0 0-12 0L6 24a12 12 0 0 0-6 10v45a12 12 0 0 0 6 10l39 23a12 12 0 0 0 12 0l39-23a12 12 0 0 0 6-10V34a12 12 0 0 0-6-10Zm-22 46h-3a4 4 0 0 0-2 1L52 80 32 69V46l20-12 17 10h3v-5L56 27a8 8 0 0 0-8 0L28 39a8 8 0 0 0-4 7v23a8 8 0 0 0 4 7l20 12a8 8 0 0 0 8 0l19-11a2 2 0 0 0 1-2v-3a2 2 0 0 0-2-2Z"/><text x="118" y="77" font-family="Arial,sans-serif" font-size="57" font-weight="600">CoreUI</text></g></svg>
            </a> -->
            @if (session('status'))
                <div class="alert alert-success mb-0" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</main>
<script src="{{ asset('vendors/@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
<script src="{{ asset('vendors/simplebar/js/simplebar.min.js') }}"></script>
<script>
document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        const showing = input.type === 'password';
        input.type = showing ? 'text' : 'password';
        button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
    });
});
</script>
@stack('scripts')
</body>
</html>
