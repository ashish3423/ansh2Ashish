@extends(request()->boolean('modal') ? 'layouts.modal' : 'layouts.newapp')

@section('title', 'Assign Contract Services')
@section('breadcrumb', 'Assign Contract Services')

@section('content')
@php
    $assignmentIds = old('assignment_ids', $assignments->pluck('id')->all());
    $formAssignments = old('assignments');
    if ($formAssignments === null) {
        $formAssignments = $assignments->map(function ($assignment) use ($contract) {
            return $contract->contract_type === 'Manpower'
                ? [
                    'assignment_id' => $assignment->id,
                    'employee_id' => $assignment->employee_id,
                ]
                : [
                    'assignment_id' => $assignment->id,
                    'service_vehicle_id' => $assignment->service_vehicle_id,
                    'driver_id' => $assignment->driver_id,
                    'assistant_id' => $assignment->assistant_id,
                    'crew_only' => ! $assignment->service_vehicle_id,
                ];
        })->all();
    } else {
        foreach ($formAssignments as $index => &$assignment) {
            if (is_array($assignment)) {
                $assignment['assignment_id'] = $assignmentIds[$index] ?? null;
            } else {
                $assignment = ['assignment_id' => $assignmentIds[$index] ?? null];
            }
        }
        unset($assignment);
    }
@endphp
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Assign Services</h4>
            <p class="text-muted mb-0">
                {{ $contract->contract_number }} ({{ $contract->contract_type }})
            </p>
        </div>
        @unless(request()->boolean('modal'))
            <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">Back to Contracts</a>
        @endunless
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

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">{{ $contract->contract_type === 'Manpower' ? 'Contract Manpower' : 'Contract Vehicles and Crew' }}</h5>
        </div>
        <div class="card-body">
            @if(in_array($contract->status, ['completed', 'terminated'], true))
                <div class="alert alert-info">
                    This contract is {{ $contract->status }}. Its resources are available for other contracts;
                    move any remaining resources below.
                </div>
            @else
                @if($contract->contract_type !== 'Manpower' && $vehicles->isEmpty())
                    <div class="alert alert-warning">
                        No active {{ strtolower($contract->contract_type) }} vehicles are registered.
                        <a href="{{ route('service-vehicles.index') }}">Add a vehicle</a> before assigning this contract.
                    </div>
                @endif
                @if($employees->isEmpty())
                    <div class="alert alert-warning">No employees are registered to assign.</div>
                @endif

                <form id="serviceAssignmentsForm" method="POST" action="{{ route('contracts.services.update', [
                    $contract->id,
                    ...($modalMode ? ['modal' => 1] : []),
                ]) }}">
                    @csrf
                    @method('PUT')
                    <div id="assignmentRows" class="vstack gap-3"></div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary" id="addAssignment">
                            <i class="bi bi-plus-circle"></i> Add {{ $contract->contract_type === 'Manpower' ? 'Employee' : 'Vehicle and Crew' }}
                        </button>
                        <button type="submit" class="btn btn-primary">Save Assignments</button>
                    </div>
                    <p class="text-muted small mt-3 mb-0">
                        Saving with no rows removes all current assignments from this contract.
                        Ambulances require a vehicle; drivers and assistants are optional.
                    </p>
                </form>
            @endif

            @can('records.update')
            @if($assignments->isNotEmpty())
                <hr class="my-4">
                <h5>Move resources separately</h5>
                <p class="text-muted small">Move a vehicle without its crew, or move employees independently to manpower or an available vehicle crew position.</p>
                @foreach($assignments as $assignment)
                    <div class="border rounded p-3 mb-2" data-assignment-id="{{ $assignment->id }}">
                        @if($assignment->service_vehicle_id)
                            <div class="mb-2">
                                <strong>{{ $assignment->registration_number }}</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2 move-resource-btn"
                                        data-url="{{ route('contracts.services.move.edit', [$contract->id, $assignment->id, 'resource' => 'vehicle', 'modal' => 1]) }}">Move vehicle only</button>
                            </div>
                        @endif
                        @if($assignment->employee_id)
                            <div class="mb-2">
                                <strong>{{ $assignment->employee_code }} — {{ $assignment->employee_first_name }} {{ $assignment->employee_last_name }}</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2 move-resource-btn"
                                        data-url="{{ route('contracts.services.move.edit', [$contract->id, $assignment->id, 'resource' => 'employee', 'modal' => 1]) }}">Move employee</button>
                            </div>
                        @endif
                        @if($assignment->driver_id)
                            <div class="mb-2">
                                <strong>Driver: {{ $assignment->driver_code }} — {{ $assignment->driver_first_name }} {{ $assignment->driver_last_name }}</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2 move-resource-btn"
                                        data-url="{{ route('contracts.services.move.edit', [$contract->id, $assignment->id, 'resource' => 'driver', 'modal' => 1]) }}">Move driver</button>
                            </div>
                        @endif
                        @if($assignment->assistant_id)
                            <div>
                                <strong>Assistant: {{ $assignment->assistant_code }} — {{ $assignment->assistant_first_name }} {{ $assignment->assistant_last_name }}</strong>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2 move-resource-btn"
                                        data-url="{{ route('contracts.services.move.edit', [$contract->id, $assignment->id, 'resource' => 'assistant', 'modal' => 1]) }}">Move assistant</button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
            @endcan
        </div>
    </div>
</div>

@can('records.update')
<div class="modal fade" id="moveResourceModal" tabindex="-1" aria-labelledby="moveResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moveResourceModalLabel">Move Contract Resource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="moveResourceFrame" title="Move contract resource" class="move-resource-frame"></iframe>
            </div>
        </div>
    </div>
</div>
<style>
    #moveResourceModal .move-resource-frame {
        display: block;
        width: 100%;
        height: min(65vh, 700px);
        border: 0;
    }
</style>
@endcan
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalMode = @json($modalMode);
    const assignmentForm = document.getElementById('serviceAssignmentsForm');
    if (assignmentForm) assignmentForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const submitButton = assignmentForm.querySelector('[type="submit"]');
        submitButton.disabled = true;
        try {
            const response = await fetch(assignmentForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(assignmentForm),
            });
            const data = await response.json();
            if (!response.ok) {
                const messages = Object.values(data.errors || {}).flat();
                throw new Error(messages.join(' ') || data.message || 'Unable to save service assignments.');
            }

            if (modalMode) {
                window.parent.postMessage({ type: 'service-assignment-saved' }, window.location.origin);
            } else {
                window.location = data.url;
            }
        } catch (error) {
            window.alert(error.message);
        } finally {
            submitButton.disabled = false;
        }
    });

    @can('records.update')
    const moveModalElement = document.getElementById('moveResourceModal');
    const moveModal = bootstrap.Modal.getOrCreateInstance(moveModalElement);
    const moveFrame = document.getElementById('moveResourceFrame');
    document.querySelectorAll('.move-resource-btn').forEach(button => {
        button.addEventListener('click', function () {
            moveFrame.src = button.dataset.url;
            moveModal.show();
        });
    });
    moveModalElement.addEventListener('hidden.bs.modal', function () {
        moveFrame.src = 'about:blank';
    });
    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || event.data?.type !== 'service-resource-moved') {
            return;
        }

        moveModal.hide();
        window.parent.postMessage({ type: 'service-assignment-saved' }, window.location.origin);
    });
    @endcan

    if (assignmentForm) {
    const type = @json($contract->contract_type);
    const employees = @json($employees->map(fn ($employee) => [
        'id' => $employee->id,
        'label' => trim($employee->employee_code . ' — ' . $employee->first_name . ' ' . ($employee->last_name ?? '')),
    ])->values());
    const vehicles = @json($vehicles->map(fn ($vehicle) => [
        'id' => $vehicle->id,
        'label' => $vehicle->registration_number . ($vehicle->make_model ? ' — ' . $vehicle->make_model : ''),
    ])->values());
    const initialAssignments = @json($formAssignments);
    const reservedEmployeeIds = @json($assignments->pluck('employee_id')->filter()->values());
    const container = document.getElementById('assignmentRows');
    const removeUrlTemplate = @json(route('contracts.services.remove', [$contract->id, '__ASSIGNMENT_ID__']));

    function selectField(label, name, options, selected, required, placeholder) {
        const wrapper = document.createElement('div');
        wrapper.className = 'col-md-4';
        const fieldLabel = document.createElement('label');
        fieldLabel.className = 'form-label';
        fieldLabel.textContent = label;
        const select = document.createElement('select');
        select.className = 'form-select';
        select.name = name;
        select.required = required;
        select.dataset.placeholder = placeholder;
        select.dataset.employeeResource = /employee_id|driver_id|assistant_id/.test(name) ? 'true' : 'false';
        select.dataset.vehicleResource = name.includes('service_vehicle_id') ? 'true' : 'false';
        const emptyOption = new Option(placeholder, '');
        select.add(emptyOption);
        options.forEach(option => select.add(new Option(option.label, option.id)));
        select.value = selected ?? '';
        wrapper.append(fieldLabel, select);
        return wrapper;
    }

    function addRow(values = {}) {
        const row = document.createElement('div');
        row.className = 'row g-3 align-items-end border rounded p-3 mx-0';
        const index = container.children.length;
        if (values.assignment_id) {
            const assignmentId = document.createElement('input');
            assignmentId.type = 'hidden';
            assignmentId.name = `assignment_ids[${index}]`;
            assignmentId.value = values.assignment_id;
            row.append(assignmentId);
        }

        if (type === 'Manpower') {
            row.append(selectField('Employee', `assignments[${index}][employee_id]`, employees, values.employee_id, true, 'Select employee'));
        } else if (values.crew_only && type !== 'Ambulance') {
            row.append(selectField('Driver (optional)', `assignments[${index}][driver_id]`, employees, values.driver_id, false, 'No driver'));
            if (type === 'Ambulance') {
                row.append(selectField('Assistant (optional)', `assignments[${index}][assistant_id]`, employees, values.assistant_id, false, 'No assistant'));
            }
        } else {
            const vehicleRequired = type === 'Ambulance';
            row.append(selectField(
                vehicleRequired ? 'Vehicle' : 'Vehicle (optional)',
                `assignments[${index}][service_vehicle_id]`,
                vehicles,
                values.service_vehicle_id,
                vehicleRequired,
                vehicleRequired ? 'Select vehicle' : 'No vehicle'
            ));
            row.append(selectField('Driver (optional)', `assignments[${index}][driver_id]`, employees, values.driver_id, false, 'No driver'));
            if (type === 'Ambulance') {
                row.append(selectField('Assistant (optional)', `assignments[${index}][assistant_id]`, employees, values.assistant_id, false, 'No assistant'));
            }
        }

        const removeWrapper = document.createElement('div');
        removeWrapper.className = 'col-auto';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-outline-danger';
        removeButton.textContent = 'Remove';
        removeButton.addEventListener('click', async function () {
            const assignmentId = values.assignment_id;
            if (assignmentId) {
                removeButton.disabled = true;
                try {
                    const response = await fetch(removeUrlTemplate.replace('__ASSIGNMENT_ID__', assignmentId), {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': assignmentForm.querySelector('input[name="_token"]').value,
                        },
                    });
                    const data = await response.json();
                    if (!response.ok) {
                        const messages = Object.values(data.errors || {}).flat();
                        throw new Error(messages.join(' ') || data.message || 'Unable to remove this assignment.');
                    }
                    document.querySelector(`[data-assignment-id="${assignmentId}"]`)?.remove();
                } catch (error) {
                    window.alert(error.message);
                    removeButton.disabled = false;
                    return;
                }
            }

            row.remove();
            renumberRows();
            refreshEmployeeOptions();
            refreshVehicleOptions();
        });
        removeWrapper.append(removeButton);
        row.append(removeWrapper);
        container.append(row);
    }

    function refreshEmployeeOptions() {
        const employeeSelects = Array.from(container.querySelectorAll('select[data-employee-resource="true"]'));
        const selectedEmployeeIds = new Set([
            ...reservedEmployeeIds.map(String),
            ...employeeSelects.map(select => select.value).filter(Boolean),
        ]);

        employeeSelects.forEach(select => {
            const selected = select.value;
            const unavailableToOtherFields = new Set(selectedEmployeeIds);
            unavailableToOtherFields.delete(selected);

            select.replaceChildren(new Option(select.dataset.placeholder, ''));
            employees.forEach(employee => {
                if (!unavailableToOtherFields.has(String(employee.id))) {
                    select.add(new Option(employee.label, employee.id));
                }
            });
            select.value = selected;
        });
    }

    function refreshVehicleOptions() {
        const vehicleSelects = Array.from(container.querySelectorAll('select[data-vehicle-resource="true"]'));
        const selectedVehicleIds = new Set(vehicleSelects.map(select => select.value).filter(Boolean));

        vehicleSelects.forEach(select => {
            const selected = select.value;
            const unavailableToOtherFields = new Set(selectedVehicleIds);
            unavailableToOtherFields.delete(selected);

            select.replaceChildren(new Option(select.dataset.placeholder, ''));
            vehicles.forEach(vehicle => {
                if (!unavailableToOtherFields.has(String(vehicle.id))) {
                    select.add(new Option(vehicle.label, vehicle.id));
                }
            });
            select.value = selected;
        });
    }

    function renumberRows() {
        Array.from(container.children).forEach((row, index) => {
            row.querySelectorAll('select').forEach(select => {
                select.name = select.name.replace(/assignments\[\d+\]/, `assignments[${index}]`);
            });
            const assignmentId = row.querySelector('input[name^="assignment_ids"]');
            if (assignmentId) {
                assignmentId.name = `assignment_ids[${index}]`;
            }
        });
    }

    container.addEventListener('change', function (event) {
        if (event.target.matches('select[data-employee-resource="true"]')) {
            refreshEmployeeOptions();
        }
        if (event.target.matches('select[data-vehicle-resource="true"]')) {
            refreshVehicleOptions();
        }
    });
    document.getElementById('addAssignment').addEventListener('click', () => {
        addRow();
        refreshEmployeeOptions();
        refreshVehicleOptions();
    });
    initialAssignments.forEach(assignment => addRow(assignment));
    refreshEmployeeOptions();
    refreshVehicleOptions();
    }
});
</script>
@endsection
