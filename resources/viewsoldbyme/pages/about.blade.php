@extends('frontlayouts.app')

@section('title', 'About Us | Ansh Enterprises')
@section('meta_description', 'Learn about Ansh Enterprises and our people-first healthcare approach.')

@section('content')
<header class="inner-hero">
    <div class="container">
        <div class="eyebrow"><span></span> About Ansh Enterprises</div>
        <h1>Healthcare with a <em>human touch.</em></h1>
        <p class="lead mt-4">We’re building a more connected healthcare experience—helping people find support, coordinate services and feel cared for at every step.</p>
        <a href="{{ route('contact') }}" class="btn btn-primary btn-lg rounded-pill px-4 mt-3">Talk to Our Team <i class="bi bi-arrow-right ms-2"></i></a>
    </div>
</header>

<section class="section-padding">
    <div class="container"><div class="row align-items-center g-5">
        <div class="col-lg-6"><img class="about-photo" src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1200&q=85" alt="Healthcare professional supporting a patient"></div>
        <div class="col-lg-6">
            <div class="eyebrow">Our purpose</div><h2>Make care easier to <em>access and navigate.</em></h2>
            <p class="lead-sm">Ansh Enterprises brings healthcare services and support together in one approachable experience. From ambulance coordination to home healthcare and consultations, our goal is to make the next step clearer for patients and families.</p>
            <p class="lead-sm">We believe good service starts with listening, clear communication and treating every person with dignity.</p>
        </div>
    </div></div>
</section>

<section class="stats-strip"><div class="container"><div class="row g-4 text-center">
    <div class="col-6 col-lg-3"><strong>People first</strong><span>Care-led approach</span></div>
    <div class="col-6 col-lg-3"><strong>Connected</strong><span>Services and support</span></div>
    <div class="col-6 col-lg-3"><strong>Responsive</strong><span>Clear communication</span></div>
    <div class="col-6 col-lg-3"><strong>Accessible</strong><span>Designed around needs</span></div>
</div></div></section>

<section class="section-padding"><div class="container">
    <div class="section-head text-center center"><div class="eyebrow">What guides us</div><h2>Care that starts with <em>people.</em></h2></div>
    <div class="row g-4 mt-3">
        <div class="col-md-4"><div class="service-card"><div class="service-icon"><i class="bi bi-heart-pulse"></i></div><h3>Compassion</h3><p>Listen carefully, act respectfully and keep people at the centre of every interaction.</p></div></div>
        <div class="col-md-4"><div class="service-card"><div class="service-icon"><i class="bi bi-chat-square-text"></i></div><h3>Clarity</h3><p>Make service options and next steps easier to understand, without unnecessary complexity.</p></div></div>
        <div class="col-md-4"><div class="service-card"><div class="service-icon"><i class="bi bi-link-45deg"></i></div><h3>Coordination</h3><p>Help patients and families connect with the support that fits their needs.</p></div></div>
    </div>
</div></section>

<section class="cta-section"><div class="container"><div class="cta-box">
    <div><div class="eyebrow">Let’s connect</div><h2>How can we <em>help you?</em></h2><p>Contact our team to discuss a service or ask a question.</p></div>
    <a href="{{ route('contact') }}" class="btn btn-dark btn-lg rounded-pill px-4">Contact Us <i class="bi bi-arrow-right ms-2"></i></a>
</div></div></section>
@endsection
