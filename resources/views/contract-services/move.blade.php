@extends(request()->boolean('modal') ? 'layouts.modal' : 'layouts.newapp')

@section('title', 'Move Contract Resource')
@section('breadcrumb', 'Move Contract Resource')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Move {{ ucfirst($resource) }}</h4>
            <p class="text-muted mb-0">{{ $label }} from {{ $sourceContract->contract_number }}</p>
        </div>
        @unless(request()->boolean('modal'))
            <a href="{{ route('contracts.services.edit', $contractId) }}" class="btn btn-outline-secondary">Back to Assignments</a>
        @endunless
    </div>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div id="moveFormAlert" class="alert alert-danger d-none" role="alert"></div>
            <form id="moveResourceForm" method="POST" action="{{ route('contracts.services.move', [$contractId, $assignmentId]) }}">
                @csrf
                <input type="hidden" name="resource" value="{{ $resource }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="target_contract_id" class="form-label">Destination contract</label>
                        <select name="target_contract_id" id="target_contract_id" class="form-select" required>
                            <option value="">Select destination contract</option>
                            @foreach($targets as $target)
                                @if($resource !== 'vehicle' || $target->contract_type === $sourceContract->contract_type)
                                    <option value="{{ $target->id }}" data-type="{{ $target->contract_type }}"
                                            @selected((string) old('target_contract_id') === (string) $target->id)>
                                        {{ $target->contract_number }} ({{ $target->contract_type }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    @if($resource !== 'vehicle')
                    <div class="col-md-6">
                        <label for="target_role" class="form-label">Destination role</label>
                        <select name="target_role" id="target_role" class="form-select" required>
                            <option value="">Select destination contract first</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-none" id="targetVehicleGroup">
                        <label for="target_assignment_id" class="form-label">Destination vehicle</label>
                        <select name="target_assignment_id" id="target_assignment_id" class="form-select">
                            <option value="">Select vehicle</option>
                        </select>
                    </div>
                    @else
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            Only the vehicle will move. Its driver and assistant will remain on the source contract.
                        </div>
                    </div>
                    @endif
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Move Resource</button>
                    @unless(request()->boolean('modal'))
                        <a href="{{ route('contracts.services.edit', $contractId) }}" class="btn btn-outline-secondary">Cancel</a>
                    @endunless
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $targetOptions = $targets->map(fn ($target) => [
        'id' => $target->id,
        'type' => $target->contract_type,
    ])->values();
    $targetVehicleOptions = $targetVehicles->map(fn ($vehicle) => [
        'assignmentId' => $vehicle->assignment_id,
        'contractId' => $vehicle->contract_id,
        'type' => $vehicle->contract_type,
        'label' => $vehicle->registration_number,
        'driverOpen' => ! $vehicle->driver_id,
        'assistantOpen' => ! $vehicle->assistant_id,
    ])->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('moveResourceForm');
    const alertBox = document.getElementById('moveFormAlert');
    const targetContractId = document.getElementById('target_contract_id');
    const resource = @json($resource);
    const modalMode = @json($modalMode);

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        alertBox.classList.add('d-none');
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const data = await response.json();
            if (!response.ok) {
                const messages = Object.values(data.errors || {}).flat();
                throw new Error(messages.join(' ') || data.message || 'Unable to move this resource.');
            }

            if (modalMode) {
                window.parent.postMessage({ type: 'service-resource-moved' }, window.location.origin);
            } else {
                window.location = data.url;
            }
        } catch (error) {
            alertBox.textContent = error.message;
            alertBox.classList.remove('d-none');
        }
    });

    if (resource === 'vehicle') return;

    const targets = @json($targetOptions);
    const vehicles = @json($targetVehicleOptions);
    const roleSelect = document.getElementById('target_role');
    const vehicleGroup = document.getElementById('targetVehicleGroup');
    const vehicleSelect = document.getElementById('target_assignment_id');

    function updateVehicleOptions() {
        const target = targets.find(item => String(item.id) === targetContractId.value);
        const role = roleSelect.value;
        const needsVehicle = target && target.type !== 'Manpower';
        vehicleGroup.classList.toggle('d-none', !needsVehicle);
        vehicleSelect.required = Boolean(needsVehicle);
        vehicleSelect.replaceChildren(new Option('Select vehicle', ''));
        if (!needsVehicle || !role) return;

        vehicles.filter(vehicle =>
            String(vehicle.contractId) === String(target.id)
            && (role === 'driver' ? vehicle.driverOpen : vehicle.assistantOpen)
        ).forEach(vehicle => vehicleSelect.add(new Option(vehicle.label, vehicle.assignmentId)));
    }

    function updateRoles() {
        const target = targets.find(item => String(item.id) === targetContractId.value);
        roleSelect.replaceChildren(new Option(target ? 'Select destination role' : 'Select destination contract first', ''));
        if (!target) {
            updateVehicleOptions();
            return;
        }

        const roles = target.type === 'Manpower'
            ? [['employee', 'Employee']]
            : target.type === 'Ambulance'
                ? [['driver', 'Driver'], ['assistant', 'Assistant']]
                : [['driver', 'Driver']];
        roles.forEach(([value, label]) => roleSelect.add(new Option(label, value)));
        roleSelect.value = @json(old('target_role', ''));
        updateVehicleOptions();
    }

    targetContractId.addEventListener('change', updateRoles);
    roleSelect.addEventListener('change', updateVehicleOptions);
    updateRoles();
});
</script>
@endsection
