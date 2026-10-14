@extends('frontlayouts.app')

@section('title', 'Contact Us | Ansh Enterprises')
@section('meta_description', 'Contact Ansh Enterprises for healthcare service enquiries and general questions.')

@section('content')
<header class="inner-hero inner-hero-compact">
    <div class="container">
        <div class="eyebrow"><span></span> We’re here to help</div>
        <h1>Let’s start a <em>conversation.</em></h1>
        <p class="lead mt-4">Ask a question, request a service or let us know how our team can assist you.</p>
    </div>
</header>

<section class="contact-wrap"><div class="container"><div class="row g-4">
    <div class="col-lg-5">
        <div class="contact-info-card">
            <div class="eyebrow light">Contact details</div><h2>Reach out to <em>our team.</em></h2>
            <p>For service enquiries and general questions, send us a message or use the contact options below.</p>
            <div class="contact-detail"><i class="bi bi-telephone-fill"></i><div><b>Call us</b><a href="tel:+918888888888">+91 88888 88888</a><small>Replace this demo number with your official contact number.</small></div></div>
            <div class="contact-detail"><i class="bi bi-envelope-fill"></i><div><b>Email</b><a href="mailto:hello@anshenterprises.example">hello@anshenterprises.example</a><small>Replace with your official email address.</small></div></div>
            <div class="contact-detail"><i class="bi bi-clock-fill"></i><div><b>Support</b><span>Contact us for service availability and assistance.</span></div></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="contact-form-card">
            <h2>Send us a message</h2>
            <p class="text-secondary">Fields marked with * are required.</p>

            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please correct the following:</strong>
                    <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('contact.send') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="fullName">Full name *</label>
                        <input class="form-control @error('fullName') is-invalid @enderror" id="fullName" name="fullName" value="{{ old('fullName') }}" required maxlength="150" placeholder="Your name">
                        @error('fullName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email address *</label>
                        <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="you@example.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone number</label>
                        <input class="form-control @error('phone') is-invalid @enderror" type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" placeholder="Your phone number">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="service">What do you need?</label>
                        <select class="form-select @error('service') is-invalid @enderror" id="service" name="service">
                            <option value="">Choose a service</option>
                            @foreach (['Ambulance service', 'Home healthcare', 'Doctor consultation', 'Diagnostics', 'Corporate care', 'General enquiry'] as $service)
                                <option value="{{ $service }}" @selected(old('service') === $service)>{{ $service }}</option>
                            @endforeach
                        </select>
                        @error('service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="message">Message *</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required maxlength="5000" placeholder="How can we help?">{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary btn-lg rounded-pill px-4" type="submit">Send Message <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div></div></section>
@endsection
