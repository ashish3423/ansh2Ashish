@extends('layouts.newapp')

@section('title', 'Users & Roles')
@section('breadcrumb', 'Users & Roles')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h3 class="mb-1">Users &amp; Roles</h3>
        <p class="text-muted mb-0">Create accounts and manage their access.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Create User</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" minlength="8" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="role">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', \App\Models\User::ROLE_SUB_ADMIN) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Registered Users</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Role</th><th>Update Role</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-primary">{{ $user->role_label }}</span></td>
                                <td>
                                    @if (auth()->user()->is($user))
                                        <span class="text-muted">Your own role cannot be changed here.</span>
                                    @else
                                        <form method="POST" action="{{ route('users.role.update', $user) }}" class="d-flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select class="form-select" name="role" aria-label="Role for {{ $user->name }}">
                                                @foreach ($roles as $value => $label)
                                                    <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-primary" type="submit">Save</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
