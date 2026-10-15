@extends('frontlayouts.app')

@section('title', 'Ansh Enterprises — Smart Healthcare, When You Need It')
@section('meta_description', 'Fast, reliable ambulance and healthcare services, delivered with human care.')

@section('content')
<section class="hero hero-fullscreen" aria-label="Ansh Enterprises healthcare banner">
    <video class="hero-banner-video" autoplay muted loop playsinline preload="auto"
           poster="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=2000&q=85"
           aria-label="Ambulance driving on a road">
        <source src="https://upload.wikimedia.org/wikipedia/commons/b/bd/Ambulance_running_on_road.webm" type="video/webm">
    </video>
    <div class="hero-video-shade" aria-hidden="true"></div>
    <div class="container hero-fullscreen-content">
        <div class="hero-copy-full">
            <div class="eyebrow"><span></span> Healthcare that moves with you</div>
            <h1>Better care.<br><em>Right when you need it.</em></h1>
            <p class="lead mt-4">Fast, reliable ambulance and healthcare services, delivered with human care — whenever every second matters.</p>
            <div class="d-flex flex-wrap gap-3 mt-4">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg rounded-pill px-4">Book a Service <i class="bi bi-arrow-right ms-2"></i></a>
                <a href="tel:+918888888888" class="btn btn-light btn-lg rounded-pill px-4"><i class="bi bi-telephone me-2"></i> Emergency Help</a>
            </div>
            <div class="trust-row mt-5">
                <div><strong>24/7</strong><small>Support</small></div>
                <div><strong>15 min</strong><small>Fast response</small></div>
                <div><strong>500+</strong><small>Cities served</small></div>
            </div>
        </div>
    </div>
    <div class="hero-video-label"><i class="bi bi-play-circle-fill"></i><span>Healthcare in motion</span></div>
</section>

<section class="stats-strip">
    <div class="container"><div class="row g-4 text-center">
        <div class="col-6 col-lg-3"><strong>8L+</strong><span>Lives assisted</span></div>
        <div class="col-6 col-lg-3"><strong>500+</strong><span>Cities covered</span></div>
        <div class="col-6 col-lg-3"><strong>10,000+</strong><span>Healthcare partners</span></div>
        <div class="col-6 col-lg-3"><strong>24 × 7</strong><span>Care availability</span></div>
    </div></div>
</section>

<section class="section-padding" id="services">
    <div class="container">
        <div class="section-head d-lg-flex justify-content-between align-items-end">
            <div><div class="eyebrow">Our ecosystem</div><h2>Everything you need.<br><em>One simple platform.</em></h2></div>
            <p class="section-copy">A connected healthcare experience designed around patients, families, hospitals and businesses.</p>
        </div>
        <div class="row g-4 mt-2">
            @php
                $services = [
                    ['truck', 'Emergency Care', 'On-demand emergency response with trained teams and connected support.', true],
                    ['house-heart', 'Home Healthcare', 'Nursing, physiotherapy, critical care and assisted recovery at home.', false],
                    ['camera-video', 'Doctor Consultation', 'Connect with general physicians and specialists through secure teleconsultation.', false],
                    ['capsule', 'Pharmacy', 'Order medicines, wellness products and healthcare essentials from one place.', false],
                    ['droplet-half', 'Diagnostics', 'Book trusted lab tests with convenient home sample collection.', false],
                    ['building', 'Corporate Care', 'Healthcare programs that help businesses protect and support their people.', false],
                ];
            @endphp
            @foreach ($services as $service)
                <div class="col-md-6 col-lg-4">
                    <div class="service-card {{ $service[3] ? 'featured' : '' }}">
                        <div class="service-icon"><i class="bi bi-{{ $service[0] }}"></i></div>
                        <h3>{{ $service[1] }}</h3>
                        <p>{{ $service[2] }}</p>
                        <a href="{{ route('contact') }}">Explore service <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="dark-section" id="how">
    <div class="container"><div class="row align-items-center g-5">
        <div class="col-lg-5">
            <div class="eyebrow light">Simple by design</div>
            <h2>Healthcare shouldn't feel complicated.</h2>
            <p>From the first request to the final follow-up, every step is designed to be quick, transparent and easy to understand.</p>
            <a href="{{ route('contact') }}" class="btn btn-light rounded-pill px-4 mt-3">Start Your Journey <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
        <div class="col-lg-7"><div class="steps">
            <div class="step"><span>01</span><div><h4>Choose your service</h4><p>Select emergency care, home healthcare, consultation, diagnostics or another service.</p></div></div>
            <div class="step"><span>02</span><div><h4>Share your details</h4><p>Tell us what you need and where you need it. Our team coordinates the next step.</p></div></div>
            <div class="step"><span>03</span><div><h4>Get connected</h4><p>Track your service and stay informed with timely updates from our care team.</p></div></div>
        </div></div>
    </div></div>
</section>

<section class="section-padding" id="why">
    <div class="container"><div class="row align-items-center g-5">
        <div class="col-lg-6 order-lg-2">
            <div class="image-stack">
                <img class="img-main" src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1000&q=85" alt="Doctor with patient">
                <div class="mini-card"><i class="bi bi-check-circle-fill"></i><div><b>Patient-first</b><small>Every decision starts with care.</small></div></div>
            </div>
        </div>
        <div class="col-lg-6 order-lg-1">
            <div class="eyebrow">Why Ansh Enterprises</div><h2>Technology with a <em>human touch.</em></h2>
            <p class="lead-sm">We combine connected technology, qualified professionals and a responsive support network to make healthcare more accessible.</p>
            <div class="benefits">
                <div><i class="bi bi-check2-circle"></i><span><b>Fast response</b><small>Quick coordination when time matters.</small></span></div>
                <div><i class="bi bi-check2-circle"></i><span><b>Verified network</b><small>Trusted healthcare professionals and partners.</small></span></div>
                <div><i class="bi bi-check2-circle"></i><span><b>Transparent experience</b><small>Clear communication from booking to completion.</small></span></div>
                <div><i class="bi bi-check2-circle"></i><span><b>Always connected</b><small>Support whenever you need it.</small></span></div>
            </div>
        </div>
    </div></div>
</section>

<section class="testimonial-section" id="stories">
    <div class="container">
        <div class="text-center section-head center"><div class="eyebrow">People first</div><h2>Care that people <em>remember.</em></h2></div>
        <div class="row g-4 mt-3">
            <div class="col-md-4"><div class="quote-card"><div class="stars">★★★★★</div><p>“The response was incredibly quick and the entire process was simple. The team stayed in touch throughout.”</p><b>— Priya S.</b><small>New Delhi</small></div></div>
            <div class="col-md-4"><div class="quote-card"><div class="stars">★★★★★</div><p>“Booking home care was effortless. The professional who visited us was caring, punctual and knowledgeable.”</p><b>— Rahul M.</b><small>Gurugram</small></div></div>
            <div class="col-md-4"><div class="quote-card"><div class="stars">★★★★★</div><p>“A very modern healthcare experience without losing the personal touch. Highly recommended.”</p><b>— Neha K.</b><small>Mumbai</small></div></div>
        </div>
    </div>
</section>

<section class="cta-section" id="book">
    <div class="container"><div class="cta-box">
        <div><div class="eyebrow">Need care?</div><h2>Let's make healthcare<br><em>easier for you.</em></h2><p>Book a service or speak to our support team today.</p></div>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="btn btn-dark btn-lg rounded-pill px-4">Book Now <i class="bi bi-arrow-right ms-2"></i></a>
            <a href="tel:+918888888888" class="btn btn-outline-dark btn-lg rounded-pill px-4"><i class="bi bi-telephone me-2"></i> Call Us</a>
        </div>
    </div></div>
</section>
@endsection
