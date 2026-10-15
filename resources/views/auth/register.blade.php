@extends('layouts.auth')

@section('title', 'Create account')
@section('stack-class', 'text-center')

@section('content')
<section class="card p-4">
    <div class="card-body d-flex flex-column gap-4">
        <div><h1 class="h5 mb-1">Create new account</h1><p class="text-body-secondary mb-0">It only takes a moment to get started.</p></div>
        @if ($errors->any())
            <div class="alert alert-danger text-start mb-0" role="alert"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form class="row g-3 text-start" action="{{ route('register.store') }}" method="POST">
            @csrf
            <div><label class="form-label" for="name">Name</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Your name" autocomplete="name" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="your@email.com" autocomplete="off" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

            
            <div><label class="form-label" for="password">Password</label><div class="input-group"><input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required><button class="input-group-text bg-body" type="button" data-toggle-password="password" aria-label="Show password"><i class="bi bi-eye ms-1" aria-hidden="true"></i></button></div><div class="form-text">Use at least 8 characters.</div>@error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>


            <div><label class="form-label" for="password_confirmation">Confirm password</label><div class="input-group"><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm your password" autocomplete="new-password" required><button class="input-group-text bg-body" type="button" data-toggle-password="password_confirmation" aria-label="Show password"><i class="bi bi-eye ms-1" aria-hidden="true"></i></button></div></div>



            <div><label class="form-check"><input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" value="1" @checked(old('terms')) required><span class="form-check-label">I accept the terms and conditions</span></label>@error('terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
            <div><button class="btn btn-secondary btn-lg w-100" type="submit">Create new account</button></div>
        </form>
    </div>
</section>
<p class="text-body-secondary mb-0">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
@endsection
