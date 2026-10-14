@extends('layouts.newapp')

@section('content')

@section('breadcrumb', 'Employee Management')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-1">Employee Management</h3>
            <p class="text-muted mb-0">Manage employee records</p>
        </div>

        <button type="button" class="btn btn-primary" id="addEmployeeBtn">
            <i class="bi bi-plus-circle"></i> Add Employee
        </button>
    </div>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Search and Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Employee</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Name, code, email or phone"
                            value="{{ request('search') }}"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="department" class="form-label">Department</label>
                        <input
                            type="text"
                            name="department"
                            id="department"
                            class="form-control"
                            placeholder="Department"
                            value="{{ request('department') }}"
                        >
                    </div>

                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active"
                                {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive"
                                {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Search
                        </button>

                        <a href="{{ route('employees.index') }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Employee Table --}}
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Employee List</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee Code</th>
                            <th>Employee Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Joining Date</th>
                            <th>Status</th>
                            <th style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>{{ $employees->firstItem() + $loop->index }}</td>

                                <td>{{ $employee->employee_code }}</td>

                                <td>
                                    {{ $employee->first_name }}
                                    {{ $employee->last_name }}
                                </td>

                                <td>{{ $employee->email ?: '-' }}</td>

                                <td>{{ $employee->phone ?: '-' }}</td>

                                <td>{{ $employee->department ?: '-' }}</td>

                                <td>{{ $employee->designation ?: '-' }}</td>

                                <td>
                                    {{ $employee->joining_date
                                        ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y')
                                        : '-' }}
                                </td>

                                <td>
                                    @if($employee->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>

                                <td>

                                <button type="button" class="btn btn-sm btn-info view-employee-btn"
        data-employee="{{ rawurlencode(json_encode($employee)) }}">View</button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning edit-employee-btn"
                                        data-employee="{{ rawurlencode(json_encode($employee)) }}">
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger delete-employee-btn"
                                        data-id="{{ $employee->id }}"
                                        data-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    No employees found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="mt-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }}
                            of {{ $employees->total() }} employees
                        </small>
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>


{{-- Add / Edit Employee Modal --}}
<div class="modal fade" id="employeeModal" tabindex="-1"
     aria-labelledby="employeeModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <form id="employeeForm" method="POST"
                  action="{{ route('employees.store') }}">

                @csrf

                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">
                        Add Employee
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div id="formAlert" class="alert alert-danger d-none"></div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="employee_code" class="form-label">
                                Employee Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="employee_code"
                                   id="employee_code" class="form-control"
                                   required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="first_name" class="form-label">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="first_name"
                                   id="first_name" class="form-control"
                                   required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">
                                Last Name
                            </label>
                            <input type="text" name="last_name"
                                   id="last_name" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                   class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" id="phone"
                                   class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="department" class="form-label">
                                Department
                            </label>
                            <input type="text" name="department"
                                   id="department" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="designation" class="form-label">
                                Designation
                            </label>
                            <input type="text" name="designation"
                                   id="designation" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="joining_date" class="form-label">
                                Joining Date
                            </label>
                            <input type="date" name="joining_date"
                                   id="joining_date" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="leaving_date" class="form-label">
                                Leaving Date
                            </label>
                            <input type="date" name="leaving_date"
                                   id="leaving_date" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="employee_status" class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="employee_status"
                                    class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary"
                            id="saveEmployeeBtn">
                        Save Employee
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1"
     aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">
                    Confirm Delete
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete
                <strong id="deleteEmployeeName">this employee</strong>?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button" class="btn btn-danger"
                        id="confirmDeleteBtn">
                    Yes, Delete
                </button>
            </div>

        </div>
    </div>
</div>












<div class="modal fade" id="viewEmployeeModal" tabindex="-1"
     aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEmployeeModalLabel">Employee  Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    @foreach([
                        'id' => 'ID',
                         'employee_code' => 'Employee Code', 
                         'first_name' => 'First Name',                      
                        'created_at' => 'Created At',
                        'updated_at' => 'Updated At',
                    ] as $field => $label)
                        <div class="col-md-6">
                            <label class="form-label" for="view_employee_{{ $field }}">{{ $label }}</label>
                            <input type="text" class="form-control"
                                   id="view_employee_{{ $field }}" disabled>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>













@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Bootstrap must be loaded by layouts.newapp before this script.
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript is not loaded. Check layouts.newapp.');
        return;
    }

    const employeeModalElement = document.getElementById('employeeModal');
    const deleteModalElement = document.getElementById('deleteEmployeeModal');

    const employeeModal = bootstrap.Modal.getOrCreateInstance(employeeModalElement);
    const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalElement);

    const viewEmployeeModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById('viewEmployeeModal')
);

    const form = document.getElementById('employeeForm');
    const saveButton = document.getElementById('saveEmployeeBtn');
    const formAlert = document.getElementById('formAlert');

    const storeUrl = @json(route('employees.store'));
    const updateUrlTemplate = @json(url('/employees'));
    const deleteUrlTemplate = @json(url('/employees'));
    const indexUrl = @json(route('employees.index'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || form.querySelector('input[name="_token"]').value;

    let deleteEmployeeId = null;

    // Clear previous validation errors.
    function clearFormErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (field) {
            field.classList.remove('is-invalid');
        });

        form.querySelectorAll('.invalid-feedback').forEach(function (feedback) {
            feedback.textContent = '';
        });

        formAlert.classList.add('d-none');
        formAlert.textContent = '';
    }

    // Display Laravel validation errors.
    function showValidationErrors(errors) {
        clearFormErrors();

        const messages = [];

        Object.keys(errors).forEach(function (fieldName) {
            const field = form.querySelector('[name="' + fieldName + '"]');

            errors[fieldName].forEach(function (message) {
                messages.push(message);
            });

            if (field) {
                field.classList.add('is-invalid');

                const feedback = field.parentElement.querySelector('.invalid-feedback');

                if (feedback) {
                    feedback.textContent = errors[fieldName][0];
                }
            }
        });

        formAlert.textContent = messages.join(' ');
        formAlert.classList.remove('d-none');
    }

    // Read the response safely, including non-JSON server errors.
    async function readResponse(response) {
        const text = await response.text();

        try {
            return text ? JSON.parse(text) : {};
        } catch (error) {
            throw new Error(
                'The server returned an unexpected response. Check Laravel logs.'
            );
        }
    }

    // Add employee.
    document.getElementById('addEmployeeBtn').addEventListener('click', function () {
        form.reset();
        clearFormErrors();

        form.action = storeUrl;
        document.getElementById('formMethod').value = 'POST';

        document.getElementById('employeeModalLabel').textContent = 'Add Employee';
        saveButton.textContent = 'Save Employee';
        saveButton.disabled = false;

        employeeModal.show();
    });





document.addEventListener('click', function (event) {
    const button = event.target.closest('.view-employee-btn');
    if (!button) return;

    const employee = JSON.parse(
        decodeURIComponent(button.getAttribute('data-employee'))
    );

    const fields = [
        'id', 'employee_code', 'first_name', 'created_at', 'updated_at'
    ];

    fields.forEach(function (field) {
        const input = document.getElementById('view_employee_' + field);
        if (input) input.value = employee[field] || '—';
    });

    viewEmployeeModal.show();
});







    // Edit employee.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.edit-employee-btn');

        if (!button) {
            return;
        }

        try {
            const employee = JSON.parse(
                decodeURIComponent(button.getAttribute('data-employee'))
            );

            form.reset();
            clearFormErrors();

            Object.keys(employee).forEach(function (key) {
                const field = form.querySelector('[name="' + key + '"]');

                if (field && employee[key] !== null) {
                    field.value = employee[key];
                }
            });

            const updateUrl = updateUrlTemplate + '/' + employee.id;

            form.action = updateUrl;
            document.getElementById('formMethod').value = 'PUT';

            document.getElementById('employeeModalLabel').textContent = 'Edit Employee';
            saveButton.textContent = 'Update Employee';
            saveButton.disabled = false;

            employeeModal.show();

        } catch (error) {
            console.error('Unable to load employee details:', error);
            alert('Unable to load employee details. Please refresh the page.');
        }
    });

    // Save or update employee.
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        clearFormErrors();

        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: new FormData(form)
            });

            const data = await readResponse(response);

            if (response.status === 422 && data.errors) {
                showValidationErrors(data.errors);
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Unable to save employee.');
            }

            employeeModal.hide();
            window.location.href = indexUrl;

        } catch (error) {
            console.error('Save employee error:', error);

            formAlert.textContent = error.message || 'Something went wrong while saving.';
            formAlert.classList.remove('d-none');

        } finally {
            saveButton.disabled = false;

            saveButton.textContent =
                document.getElementById('formMethod').value === 'PUT'
                    ? 'Update Employee'
                    : 'Save Employee';
        }
    });

    // Open delete confirmation.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.delete-employee-btn');

        if (!button) {
            return;
        }

        deleteEmployeeId = button.getAttribute('data-id');

        document.getElementById('deleteEmployeeName').textContent =
            button.getAttribute('data-name') || 'this employee';

        document.getElementById('confirmDeleteBtn').disabled = false;

        deleteModal.show();
    });

    // Delete employee.
    document.getElementById('confirmDeleteBtn').addEventListener('click', async function () {
        if (!deleteEmployeeId) {
            return;
        }

        const button = this;
        button.disabled = true;
        button.textContent = 'Deleting...';

        try {
            const response = await fetch(
                deleteUrlTemplate + '/' + deleteEmployeeId,
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        _method: 'DELETE',
                        _token: csrfToken
                    })
                }
            );

            const data = await readResponse(response);

            if (!response.ok) {
                throw new Error(data.message || 'Unable to delete employee.');
            }

            deleteModal.hide();
            window.location.href = indexUrl;

        } catch (error) {
            console.error('Delete employee error:', error);
            alert(error.message || 'Something went wrong while deleting.');

        } finally {
            button.disabled = false;
            button.textContent = 'Yes, Delete';
        }
    });

});
</script>
@endsection
