@extends('layouts.newapp')

@section('title', 'Service Vehicles')
@section('breadcrumb', 'Service Vehicles')

@section('content')
@php
    $editingVehicle = $vehicles->firstWhere('id', (int) request('edit'));
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Service Vehicles</h4>
            <p class="text-muted mb-0">Register ambulances and cabs for contract assignments.</p>
        </div>
        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">Contracts</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @canany(['records.create', 'records.update'])
    <div class="card mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">{{ $editingVehicle ? 'Edit Vehicle' : 'Register Vehicle' }}</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ $editingVehicle ? route('service-vehicles.update', $editingVehicle->id) : route('service-vehicles.store') }}">
                @csrf
                @if($editingVehicle)
                    @method('PUT')
                @endif
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                            @foreach(['Ambulance', 'Cabs'] as $type)
                                <option value="{{ $type }}" @selected(old('vehicle_type', $editingVehicle->vehicle_type ?? '') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="registration_number" class="form-label">Registration Number</label>
                        <input name="registration_number" id="registration_number" class="form-control" maxlength="50"
                               value="{{ old('registration_number', $editingVehicle->registration_number ?? '') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="make_model" class="form-label">Make / Model</label>
                        <input name="make_model" id="make_model" class="form-control" maxlength="150"
                               value="{{ old('make_model', $editingVehicle->make_model ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $editingVehicle->status ?? 'active') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        @if($editingVehicle)
                            @can('records.update')
                                <button type="submit" class="btn btn-primary">Save</button>
                            @endcan
                        @else
                            @can('records.create')
                                <button type="submit" class="btn btn-primary">Add</button>
                            @endcan
                        @endif
                    </div>
                </div>
                @if($editingVehicle)
                    <a href="{{ route('service-vehicles.index') }}" class="btn btn-link px-0 mt-2">Cancel edit</a>
                @endif
            </form>
        </div>
    </div>
    @endcan

    <div class="card">
        <div class="card-header bg-white"><h5 class="mb-0">Registered Vehicles</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Registration Number</th>
                        <th>Make / Model</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->vehicle_type }}</td>
                            <td>{{ $vehicle->registration_number }}</td>
                            <td>{{ $vehicle->make_model ?: '-' }}</td>
                            <td>{{ ucfirst($vehicle->status) }}</td>
                            <td class="text-nowrap">
                                @can('records.update')
                                    <a href="{{ route('service-vehicles.index', ['edit' => $vehicle->id]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('records.delete')
                                    <form method="POST" action="{{ route('service-vehicles.destroy', $vehicle->id) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this vehicle? Its contract service assignments will also be removed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">No vehicles registered.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
