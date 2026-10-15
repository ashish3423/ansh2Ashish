@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
<section class="card p-4">
    <div class="card-body d-flex flex-column gap-4">
        <div class="text-center"><h1 class="h5 mb-1">Login to your account</h1><p class="text-body-secondary mb-0">Welcome back. Enter your details to continue.</p></div>
        @if ($errors->any())
            <div class="alert alert-danger mb-0" role="alert"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form class="row g-3" action="{{ url('/login') }}" method="POST">
            @csrf
            <div><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="your@email.com" autocomplete="off" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><div class="d-flex justify-content-between"><label class="form-label" for="password">Password</label></div><div class="input-group"><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="Your password" autocomplete="current-password" required><button class="input-group-text bg-body" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye ms-1" aria-hidden="true"></i></button></div>@error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
            <!-- <div><label class="form-check"><input class="form-check-input" type="checkbox" name="remember" value="1" @checked(old('remember'))><span class="form-check-label">Remember me on this device</span></label></div> -->
             <br/>
            <div><button class="w-100 btn btn-secondary btn-lg" type="submit">Sign in</button></div>
        </form>
    </div>
</section>
<p class="text-center text-body-secondary mb-0">Need an account? <a href="{{ route('register') }}">Register from here</a></p>
@endsection
