<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ansh Enterprises — Smart Healthcare, When You Need It')</title>
    <meta name="description" content="@yield('meta_description', 'Connected healthcare and ambulance support from Ansh Enterprises.')">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('main/style.css') }}">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom">
    <div class="container py-2">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
            <span class="brand-mark"><i class="bi bi-heart-pulse-fill"></i></span>
            Ansh <span>Enterprises</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-lg-3">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}#services">Services</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a></li>
            </ul>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('contact') }}" class="btn btn-outline-dark rounded-pill px-4">Contact Us</a>
                <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4">Log In</a>
            </div>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<footer>
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-white" href="{{ route('home') }}">
                    <span class="brand-mark"><i class="bi bi-heart-pulse-fill"></i></span>
                    Ansh <span>Enterprises</span>
                </a>
                <p class="footer-copy mt-3">Connected healthcare and ambulance support, with people at the heart of every step.</p>
            </div>
            <div class="col-6 col-lg-3">
                <h5>Company</h5>
                <a href="{{ route('about') }}">About Us</a>
                <a href="{{ route('contact') }}">Contact Us</a>
                <a href="{{ route('login') }}">Log In</a>
            </div>
            <div class="col-6 col-lg-4">
                <h5>Explore</h5>
                <a href="{{ route('home') }}#services">Services</a>
                <a href="{{ route('home') }}#how">How It Works</a>
                <a href="{{ route('home') }}">Home</a>
            </div>
        </div>
        <div class="footer-bottom mt-5 pt-4">
            <span>© {{ date('Y') }} Ansh Enterprises. All rights reserved.</span>
            <span>Privacy · Terms</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
