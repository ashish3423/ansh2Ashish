@extends('layouts.newapp')

@section('title', 'Contracts')
@section('breadcrumb', 'Contracts')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Contracts</h4>
            <p class="text-muted mb-0">Manage contracts</p>
        </div>
        @can('records.create')
            <button type="button" class="btn btn-primary" id="addContractBtn">
                <i class="bi bi-plus-circle"></i> Add Contract
            </button>
        @endcan
    </div>

    <div id="pageAlert" class="alert d-none" role="alert"></div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('contracts.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Search contracts</label>
                        <input type="search" name="search" id="search" class="form-control"
                               placeholder="Contract number, department, organization, or type"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_status" class="form-label">Status</label>
                        <select name="status" id="filter_status" class="form-select">
                            <option value="">All statuses</option>
                            @foreach(['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed', 'terminated' => 'Terminated'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('contracts.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><h5 class="mb-0">Contract List</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Contract Number</th>
                            <th>Type</th>
                            <th>Organization</th>
                            <th>Department</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                            <tr>
                                <td>{{ $contracts->firstItem() + $loop->index }}</td>
                                <td>{{ $contract->contract_number }}</td>
                                <td>{{ $contract->contract_type ?: '-' }}</td>
                                <td>{{ $contract->organization ?: '-' }}</td>
                                <td>{{ $contract->department ?: '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d-m-Y') }}</td>
                                <td>{{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('d-m-Y') : '-' }}</td>
                                <td>{{ $contract->amount !== null ? number_format((float) $contract->amount, 2) : '-' }}</td>
                                <td>
                                    <span class="badge {{ $contract->status === 'active' ? 'bg-success' : ($contract->status === 'terminated' ? 'bg-danger' : ($contract->status === 'completed' ? 'bg-primary' : 'bg-secondary')) }}
">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-link view-contract-btn"
                                            title="View Details" aria-label="View Details"
                                            data-contract="{{ rawurlencode(json_encode($contract)) }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @can('records.update')
                                        @if(! in_array($contract->status, ['completed', 'terminated'], true))
                                            <button type="button" class="btn btn-sm btn-outline-primary assign-services-btn"
                                                    data-url="{{ route('contracts.services.edit', [$contract->id, 'modal' => 1]) }}"
                                                    title="Assign Services">
                                                <i class="bi bi-person-gear"></i> Assign
                                            </button>
                                        @endif
                                    @endcan
                                    @can('records.update')
                                        <button type="button" class="btn btn-link edit-contract-btn"
                                                title="Edit Contract" aria-label="Edit Contract"
                                                data-contract="{{ rawurlencode(json_encode($contract)) }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    @can('records.delete')
                                        <button type="button" class="btn btn-link text-danger delete-contract-btn"
                                                title="Delete Contract" aria-label="Delete Contract"
                                                data-id="{{ $contract->id }}"
                                                data-name="{{ $contract->contract_number }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center py-4">No contracts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($contracts->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <small class="text-muted">
                        Showing {{ $contracts->firstItem() }} to {{ $contracts->lastItem() }}
                        of {{ $contracts->total() }} contracts
                    </small>
                    {{ $contracts->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="viewContractModal" tabindex="-1"
     aria-labelledby="viewContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewContractModalLabel">Contract Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body contract-details-modal-body">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <tbody>
                        @foreach([
                            'contract_number' => 'Contract Number',
                            'contract_type' => 'Contract Type',
                            'organization' => 'Organization',
                            'department' => 'Department',
                            'address' => 'Address',
                            'start_date' => 'Start Date',
                            'end_date' => 'End Date',
                            'amount' => 'Contract Amount',
                            'paid_holidays' => 'Paid Holidays (PH)',
                            'leave_with_wages' => 'Leave with Wages (LWW)',
                            'status' => 'Status',
                            'notes' => 'Notes',
                        ] as $field => $label)
                            <tr>
                                <th style="width: 35%; background-color: #f8f9fa;">{{ $label }}</th>
                                <td id="view_contract_{{ $field }}"></td>
                            </tr>
                        @endforeach
                        <tr>
                            <th style="width: 35%; background-color: #f8f9fa;">Assigned Services</th>
                            <td>
                                <ul id="view_contract_assigned_services" class="mb-0 ps-3"></ul>
                                <span id="view_contract_no_assigned_services" class="text-muted">No services assigned</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@canany(['records.create', 'records.update'])
<div class="modal fade" id="contractModal" tabindex="-1" aria-labelledby="contractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="contractForm" method="POST" action="{{ route('contracts.store') }}">
                @csrf
                <input type="hidden" name="_method" id="contractMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="contractModalLabel">Add Contract</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body contract-form-body">
                    <div id="contractFormAlert" class="alert alert-danger d-none" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contract_number" class="form-label">Contract Number <span class="text-danger">*</span></label>
                            <input name="contract_number" id="contract_number" class="form-control" maxlength="50" autocomplete="off" required>
                            <div id="contractNumberFeedback" class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select name="contract_type" id="contract_type" class="form-select" required>
                                <option value="">Select contract type</option>
                                @foreach(['Manpower', 'Ambulance', 'Cabs'] as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <input name="department" id="department" class="form-control" autocomplete="off" maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="organization" class="form-label">Organization</label>
                            <input name="organization" id="organization" class="form-control" autocomplete="off" maxlength="150">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="terminated">Terminated</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Contract Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control" min="0" step="0.01" autocomplete="off">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="3" maxlength="5000" autocomplete="off"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 d-none" id="extensionDateField">
                            <label for="extension_date" class="form-label">Extension Date</label>
                            <input type="date" name="extension_date" id="extension_date" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" maxlength="5000"></textarea>
                            <div class="invalid-feedback"></div>
                            <br/>
                        </div><div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="hidden" name="paid_holidays" value="0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="paid_holidays" id="paid_holidays" value="1">
                                <label class="form-check-label" for="paid_holidays">PH (Paid Holidays)</label>
                            </div>
                            <div class="mt-2 d-none" id="paidHolidaysPercentageField">
                                <label for="paid_holidays_percentage" class="form-label">PH Percentage</label>
                                <input type="number" name="paid_holidays_percentage" id="paid_holidays_percentage"
                                       class="form-control" min="0" max="100" step="0.01">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="hidden" name="leave_with_wages" value="0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       name="leave_with_wages" id="leave_with_wages" value="1">
                                <label class="form-check-label" for="leave_with_wages">LWW (Leave with Wages)</label>
                            </div>
                            <div class="mt-2 d-none" id="leaveWithWagesPercentageField">
                                <label for="leave_with_wages_percentage" class="form-label">LWW Percentage</label>
                                <input type="number" name="leave_with_wages_percentage" id="leave_with_wages_percentage"
                                       class="form-control" min="0" max="100" step="0.01">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveContractBtn">Save Contract</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanany

@can('records.update')
<div class="modal fade" id="serviceAssignmentModal" tabindex="-1" aria-labelledby="serviceAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable service-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceAssignmentModalLabel">Assign Contract Services</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="serviceAssignmentFrame" title="Assign contract services" class="service-modal-frame"></iframe>
            </div>
        </div>
    </div>
</div>
<style>
    #serviceAssignmentModal .service-modal-dialog {
        max-width: min(96vw, 1500px);
    }

    #serviceAssignmentModal .service-modal-frame {
        display: block;
        width: 100%;
        height: min(78vh, 900px);
        border: 0;
    }
</style>
@endcan

<style>
    #contractModal .contract-form-body {
        max-height: calc(100vh - 12rem);
        overflow-y: auto;
    }

    #viewContractModal .contract-details-modal-body {
        max-height: calc(100vh - 10rem);
        overflow-y: auto;
    }
</style>

@can('records.delete')
<div class="modal fade" id="deleteContractModal" tabindex="-1" aria-labelledby="deleteContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteContractModalLabel">Delete Contract</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete <strong id="deleteContractName"></strong>? This action cannot be undone.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteContractBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pageAlert = document.getElementById('pageAlert');
    const indexUrl = @json(route('contracts.index'));
    const storeUrl = @json(route('contracts.store'));
    const contractsUrl = @json(url('/contracts'));
    const checkContractNumberUrl = @json(route('contracts.check-number'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function showPageAlert(message, type) {
        pageAlert.textContent = message;
        pageAlert.className = 'alert alert-' + type;
        pageAlert.setAttribute('role', 'alert');
    }

    const viewContractModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('viewContractModal')
    );
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.view-contract-btn');
        if (!button) return;

        const contract = JSON.parse(decodeURIComponent(button.dataset.contract));
        const dateFields = ['start_date', 'end_date'];
        const booleanFields = ['paid_holidays', 'leave_with_wages'];

        

        Object.entries(contract).forEach(([field, value]) => {
        //alert(value);
            const cell = document.getElementById('view_contract_' + field);
            if (!cell) return;

            if (dateFields.includes(field) && value) {
                const [year, month, day] = value.slice(0, 10).split('-');
                value = `${day}-${month}-${year}`;
            } else if (booleanFields.includes(field)) {
                const percentage = contract[field + '_percentage'];
                value = Number(value) === 1
                    ? 'Yes' + (percentage !== null && percentage !== '' ? ` (${percentage}%)` : '')
                    : 'No';
            } else if (field === 'amount' && value !== null && value !== '') {
                value = Number(value).toFixed(2);
            } else if (field === 'status' && value) {
                value = value.charAt(0).toUpperCase() + value.slice(1);
            }

            cell.textContent = value === null || value === '' ? '—' : value;
        });

        const assignedServicesList = document.getElementById('view_contract_assigned_services');
        const noAssignedServices = document.getElementById('view_contract_no_assigned_services');
        assignedServicesList.replaceChildren();
        (contract.assigned_services || []).forEach(service => {
            const item = document.createElement('li');
            item.textContent = service;
            assignedServicesList.appendChild(item);
        });
        noAssignedServices.classList.toggle('d-none', assignedServicesList.childElementCount > 0);

        document.getElementById('viewContractModalLabel').textContent =
            'Contract Details — ' + contract.contract_number;
        viewContractModal.show();
    });

    async function readResponse(response) {
        const text = await response.text();
        try {
            return text ? JSON.parse(text) : {};
        } catch {
            throw new Error('The server returned an unexpected response. Check Laravel logs.');
        }
    }

    @can('records.update')
    const serviceAssignmentModalElement = document.getElementById('serviceAssignmentModal');
    const serviceAssignmentModal = bootstrap.Modal.getOrCreateInstance(serviceAssignmentModalElement);
    const serviceAssignmentFrame = document.getElementById('serviceAssignmentFrame');

    document.querySelectorAll('.assign-services-btn').forEach(button => {
        button.addEventListener('click', function () {
            serviceAssignmentFrame.src = button.dataset.url;
            serviceAssignmentModal.show();
        });
    });

    serviceAssignmentModalElement.addEventListener('hidden.bs.modal', function () {
        serviceAssignmentFrame.src = 'about:blank';
    });

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || event.data?.type !== 'service-assignment-saved') {
            return;
        }

        serviceAssignmentModal.hide();
        window.location.reload();
    });
    @endcan

    @canany(['records.create', 'records.update'])
    const form = document.getElementById('contractForm');
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('contractModal'));
    const formAlert = document.getElementById('contractFormAlert');
    const saveButton = document.getElementById('saveContractBtn');
    const contractNumberField = document.getElementById('contract_number');
    const contractNumberFeedback = document.getElementById('contractNumberFeedback');
    const extensionDateField = document.getElementById('extensionDateField');
    const paidHolidays = document.getElementById('paid_holidays');
    const leaveWithWages = document.getElementById('leave_with_wages');
    const paidHolidaysPercentageField = document.getElementById('paidHolidaysPercentageField');
    const leaveWithWagesPercentageField = document.getElementById('leaveWithWagesPercentageField');
    let contractBeingEdited = null;
    let contractNumberCheckSequence = 0;
    let contractNumberCheckTimer = null;

    function clearFormErrors() {
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        form.querySelectorAll('.is-valid').forEach(field => field.classList.remove('is-valid'));
        form.querySelectorAll('.invalid-feedback, .valid-feedback, .form-text').forEach(feedback => {
            feedback.className = 'invalid-feedback';
            feedback.textContent = '';
        });
        formAlert.classList.add('d-none');
        formAlert.textContent = '';
    }

    function setContractNumberFeedback(message, isAvailable) {
        const feedback = contractNumberFeedback;
        if (isAvailable === null) {
            contractNumberField.classList.remove('is-invalid', 'is-valid');
            feedback.className = 'form-text d-block';
            feedback.textContent = message;
            return;
        }
        contractNumberField.classList.toggle('is-invalid', !isAvailable);
        contractNumberField.classList.toggle('is-valid', isAvailable);
        feedback.textContent = message;
        if (isAvailable) {
            feedback.classList.remove('invalid-feedback');
            feedback.classList.add('valid-feedback', 'd-block');
        } else {
            feedback.classList.remove('valid-feedback', 'd-block');
            feedback.classList.add('invalid-feedback');
        }
    }

    async function checkContractNumber() {
        const contractNumber = contractNumberField.value.trim();
        if (!contractNumber) {
            return true;
        }

        const sequence = ++contractNumberCheckSequence;
        const url = new URL(checkContractNumberUrl, window.location.origin);
        url.searchParams.set('contract_number', contractNumber);
        if (contractBeingEdited) {
            url.searchParams.set('exclude_id', contractBeingEdited);
        }

        setContractNumberFeedback('Checking contract number...', null);
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await readResponse(response);
            if (!response.ok) {
                throw new Error(data.message || 'Unable to verify the contract number.');
            }
            if (sequence !== contractNumberCheckSequence) {
                return false;
            }
            if (!data.available) {
                setContractNumberFeedback('This contract number is already in use.', false);
                return false;
            }
            setContractNumberFeedback('Contract number is available.', true);
            return true;
        } catch (error) {
            if (sequence === contractNumberCheckSequence) {
                setContractNumberFeedback(error.message, false);
            }
            return false;
        }
    }

    contractNumberField.addEventListener('input', function () {
        contractNumberCheckSequence++;
        clearTimeout(contractNumberCheckTimer);
        contractNumberField.classList.remove('is-invalid', 'is-valid');
        contractNumberFeedback.className = 'invalid-feedback';
        contractNumberFeedback.textContent = '';
        if (contractNumberField.value.trim()) {
            contractNumberCheckTimer = setTimeout(checkContractNumber, 300);
        }
    });

    function showValidationErrors(errors) {
        clearFormErrors();
        const messages = [];
        Object.entries(errors).forEach(([name, fieldErrors]) => {
            const field = form.querySelector('[name="' + name + '"]');
            messages.push(...fieldErrors);
            if (field) {
                field.classList.add('is-invalid');
                const feedback = field.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = fieldErrors[0];
            }
        });
        formAlert.textContent = messages.join(' ');
        formAlert.classList.remove('d-none');
    }

    function syncConditionalFields() {
        extensionDateField.classList.toggle('d-none', !contractBeingEdited);
        paidHolidaysPercentageField.classList.toggle('d-none', !paidHolidays.checked);
        leaveWithWagesPercentageField.classList.toggle('d-none', !leaveWithWages.checked);
    }

    paidHolidays.addEventListener('change', syncConditionalFields);
    leaveWithWages.addEventListener('change', syncConditionalFields);

    document.getElementById('addContractBtn')?.addEventListener('click', function () {
        form.reset();
        clearFormErrors();
        contractBeingEdited = null;
        syncConditionalFields();
        form.action = storeUrl;
        document.getElementById('contractMethod').value = 'POST';
        document.getElementById('contractModalLabel').textContent = 'Add Contract';
        saveButton.textContent = 'Save Contract';
        modal.show();
    });

    document.querySelectorAll('.edit-contract-btn').forEach(button => {
        button.addEventListener('click', function () {
            const contract = JSON.parse(decodeURIComponent(button.dataset.contract));
            form.reset();
            clearFormErrors();
            contractBeingEdited = contract.id;
            Object.keys(contract).forEach(key => {
                const checkbox = form.querySelector('input[type="checkbox"][name="' + key + '"]');
                if (checkbox) {
                    checkbox.checked = contract[key] === true || contract[key] === 1 || contract[key] === '1';
                    return;
                }
                const field = form.elements.namedItem(key);
                if (field) field.value = contract[key] ?? '';
            });
            syncConditionalFields();
            form.action = contractsUrl + '/' + contract.id;
            document.getElementById('contractMethod').value = 'PUT';
            document.getElementById('contractModalLabel').textContent = 'Edit Contract';
            saveButton.textContent = 'Update Contract';
            modal.show();
        });
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearFormErrors();
        saveButton.disabled = true;
        try {
            clearTimeout(contractNumberCheckTimer);
            if (!await checkContractNumber()) {
                return;
            }
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            const data = await readResponse(response);
            if (response.status === 422) {
                showValidationErrors(data.errors || { contract: [data.message || 'Please check the form.'] });
                return;
            }
            if (!response.ok) throw new Error(data.message || 'Unable to save the contract.');
            window.location = indexUrl;
        } catch (error) {
            showValidationErrors({ contract: [error.message] });
        } finally {
            saveButton.disabled = false;
        }
    });
    @endcanany

    @can('records.delete')
    const deleteModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteContractModal'));
    const confirmDeleteButton = document.getElementById('confirmDeleteContractBtn');
    let contractToDelete = null;

    document.querySelectorAll('.delete-contract-btn').forEach(button => {
        button.addEventListener('click', function () {
            contractToDelete = button.dataset.id;
            document.getElementById('deleteContractName').textContent = button.dataset.name;
            deleteModal.show();
        });
    });

    confirmDeleteButton.addEventListener('click', async function () {
        if (!contractToDelete) return;
        confirmDeleteButton.disabled = true;
        try {
            const response = await fetch(contractsUrl + '/' + contractToDelete, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await readResponse(response);
            if (!response.ok) throw new Error(data.message || 'Unable to delete the contract.');
            window.location = indexUrl;
        } catch (error) {
            deleteModal.hide();
            showPageAlert(error.message, 'danger');
        } finally {
            confirmDeleteButton.disabled = false;
        }
    });
    @endcan
});
</script>
@endsection
