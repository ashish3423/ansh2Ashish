@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="card p-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div><p class="text-body-secondary mb-1">Dashboard</p><h1 class="h3 mb-2">Welcome, {{ auth()->user()->name }}</h1><p class="mb-0">You are signed in to your account successfully now.</p></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary" type="submit">Sign out</button></form>
        </div>
    </div>
</section>
@endsection
