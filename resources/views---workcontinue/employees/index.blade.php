@extends('layouts.newapp')

@section('content')

@section('breadcrumb', 'Employee Management')

<div class="container-fluid py-4 employee-management-page">

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
                            placeholder="Name, code, email, work location, or phone"
                            value="{{ request('search') }}"
                        >
                    </div>

                    <!-- <div class="col-md-3">
                        <label for="department" class="form-label">Department</label>
                        <input
                            type="text"
                            name="department"
                            id="department"
                            class="form-control"
                            placeholder="Department"
                            value="{{ request('department') }}"
                        >
                    </div> -->

                    
<div class="col-md-3">
    <label for="filter_department" class="form-label">
        Department
    </label>

    <select
        name="department"
        id="filter_department"
        class="form-select"
    >
        <option value="">All Departments</option>

        @foreach($departments as $department)
            <option
                value="{{ $department }}"
                {{ request('department') === $department ? 'selected' : '' }}
            >
                {{ $department }}
            </option>
        @endforeach
    </select>
</div>





{{-- Police Verification Filter --}}
<div class="col-md-3">
    <label for="filter_police_verification" class="form-label">
        Police Verification
    </label>

    <select name="police_verification_status"
            id="filter_police_verification"
            class="form-select">
        <option value="">All</option>
        <option value="1"
            {{ request('police_verification_status') === '1' ? 'selected' : '' }}>
            Verified
        </option>
        <option value="0"
            {{ request('police_verification_status') === '0' ? 'selected' : '' }}>
            Not Verified
        </option>
    </select>
</div>

{{-- ID Card Status Filter --}}
<div class="col-md-3">
    <label for="filter_id_card_status" class="form-label">
        ID Card Status
    </label>

    <select name="id_card_status"
            id="filter_id_card_status"
            class="form-select">
        <option value="">All</option>
        <option value="1"
            {{ request('id_card_status') === '1' ? 'selected' : '' }}>
            Issued
        </option>
        <option value="0"
            {{ request('id_card_status') === '0' ? 'selected' : '' }}>
            Not Issued
        </option>
    </select>
</div>

{{-- Dress Status Filter --}}
<div class="col-md-3">
    <label for="filter_dress_status" class="form-label">
        Dress Status
    </label>

    <select name="dress_status"
            id="filter_dress_status"
            class="form-select">
        <option value="">All</option>
        <option value="1"
            {{ request('dress_status') === '1' ? 'selected' : '' }}>
            Issued
        </option>
        <option value="0"
            {{ request('dress_status') === '0' ? 'selected' : '' }}>
            Not Issued
        </option>
    </select>
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
                        <button type="submit" class="btn btn-primary" id="employeeSearchBtn">
                            Search
                        </button>

                        <!-- <a href="{{ route('employees.index') }}"
                           class="btn btn-secondary">
                            Reset
                        </a> -->

                        <a href="{{ route('employees.index') }}"
   class="btn btn-secondary"
   id="resetEmployeeFilters">
    Reset
</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
<style>

#employeeTableCard {
    position: relative;
    min-height: 200px;
}

.employee-table-loader {
    position: absolute;
    inset: 0;
    z-index: 20;
    background: rgba(255, 255, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    border-radius: 4px;
}

.employee-table-loader.d-none {
    display: none !important;
}
</style>
    {{-- Employee Table --}}
   
@include('employees.partials.table', ['employees' => $employees])




<style>
/* Force vertical scrolling inside the Add/Edit Employee modal */
#employeeModal .modal-dialog {
    max-height: calc(100vh - 2rem) !important;
    margin-top: 1rem !important;
    margin-bottom: 1rem !important;
}

#employeeModal .modal-content {
    max-height: calc(100vh - 2rem) !important;
    overflow: hidden !important;
}

#employeeModal #employeeForm {
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    overflow: hidden !important;
}

#employeeModal .modal-body {
    min-height: 0 !important;
    max-height: calc(100vh - 200px) !important;
    overflow-y: scroll !important;
    overflow-x: hidden !important;
    flex: 1 1 auto !important;
}

/* Visual grouping for Add/Edit Employee form sections */
#employeeModal .employee-form-section {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    margin-top: 8px;
    margin-bottom: 2px;
    border-left: 4px solid #0d6efd;
    border-radius: 4px;
    background: #f1f6ff;
    color: #173b70;
    font-size: 1rem;
    font-weight: 600;
}

#employeeModal .employee-form-section i {
    font-size: 1.1rem;
}
</style>


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

                        {{-- Section 1: Personal Details --}}
                        <div class="col-12">
                            <div class="employee-form-section">
                                <i class="bi bi-person-vcard me-2"></i>Personal Details
                            </div>
                        </div>

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
                            <label for="work_location" class="form-label">
                                Work Location
                            </label>
                            <input type="text" name="work_location"
                                   id="work_location" class="form-control">
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
                            <label for="aadhar_number" class="form-label">
                                Aadhar Number
                            </label>
                            <input type="text" name="aadhar_number"
                                   id="aadhar_number" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>


                        <div class="col-md-6">
                            <label for="driving_license_number" class="form-label">
                                Driving License Number
                            </label>
                            <input type="text" name="driving_license_number"
                                   id="driving_license_number" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>



                        {{-- Section 2: Bank Details --}}
                        <div class="col-12">
                            <div class="employee-form-section">
                                <i class="bi bi-bank me-2"></i>Bank Details
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="account_number" class="form-label">
                                Account Number
                            </label>
                            <input type="text" name="account_number"
                                   id="account_number" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>


                        <div class="col-md-6">
                            <label for="ifsc_code" class="form-label">
                                IFSC Code
                            </label>
                            <input type="text" name="ifsc_code"
                                   id="ifsc_code" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>



                        {{-- Section 3: Other Details --}}
                        <div class="col-12">
                            <div class="employee-form-section">
                                <i class="bi bi-card-list me-2"></i>Other Details
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="uan_number" class="form-label">
                                UAN Number
                            </label>
                            <input type="text" name="uan_number"
                                   id="uan_number" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>



                        <div class="col-md-6">
                            <label for="esi_number" class="form-label">
                                ESI Number
                            </label>
                            <input type="text" name="esi_number"
                                   id="esi_number" class="form-control">
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

                        <!-- <div class="col-md-6">
                            <label for="leaving_date" class="form-label">
                                Leaving Date
                            </label>
                            <input type="date" name="leaving_date"
                                   id="leaving_date" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div> -->




                        <!-- New Code Added -->
<div class="col-md-6"></div>
<p></p>                        
{{-- ID Card Status --}}
<div class="col-md-4">
    <label class="form-label d-block">ID Card Status</label>

    <div class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            role="switch"
            name="id_card_status"
            id="id_card_status"
            value="1"
        >
        <label class="form-check-label" for="id_card_status">
            Issued
        </label>
    </div>
    <div class="invalid-feedback"></div>
</div>


{{-- Dress Status --}}
<div class="col-md-4">
    <label class="form-label d-block">Dress Status</label>

    <div class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            role="switch"
            name="dress_status"
            id="dress_status"
            value="1"
        >
        <label class="form-check-label" for="dress_status">
            Issued
        </label>
    </div>
    <div class="invalid-feedback"></div>
</div>

{{-- Police Verification Status --}}
<div class="col-md-4">
    <label class="form-label d-block">Police Verification Status</label>

    <div class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            role="switch"
            name="police_verification_status"
            id="police_verification_status"
            value="1"
        >
        <label class="form-check-label"
               for="police_verification_status">
            Verified
        </label>
    </div>
    <div class="invalid-feedback"></div>
</div>

{{-- Police Certificate Number --}}
<div class="col-md-6 d-none" id="police_certificate_wrapper">
    <label for="police_certificate_number" class="form-label">
        Police Certificate Number
    </label>

    <input
        type="text"
        name="police_certificate_number"
        id="police_certificate_number"
        class="form-control"
        placeholder="Enter police certificate number"
    >
    <div class="invalid-feedback"></div>
</div>



                        <!-- New Code Added -->







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
                <!-- <div class="row g-3">
                    @foreach([
                        'id' => 'ID',
                         'employee_code' => 'Employee Code', 
                         'first_name' => 'First Name',                      
                        'created_at' => 'Created At',
                        'updated_at' => 'Updated At',
                    ] as $field => $label)

                    <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                    <th>{{ $label }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                    <td id="view_employee_{{ $field }}"></td>
                    </tr>
                    </tbody>
                    </table>
                                








                        <div class="col-md-6">
                            <label class="form-label" for="view_employee_{{ $field }}">{{ $label }}</label>
                            <input type="text" class="form-control"
                                   id="view_employee_{{ $field }}" disabled>
                        </div> 
                    @endforeach
                </div> -->












<table class="table table-bordered table-hover align-middle">
    <tbody>
        @foreach([
            'id' => 'ID',
            'employee_code' => 'Employee Code',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'work_location' => 'Work Location',
            'department' => 'Department',
            'designation' => 'Designation',
            'aadhar_number' => 'Aadhar Number',
            'driving_license_number' => 'Driving License Number',
            'account_number' => 'Account Number',
            'ifsc_code' => 'IFSC Code',
            'uan_number' => 'UAN Number',
            'esi_number' => 'ESI Number',
            'joining_date' => 'Joining Date',
            'leaving_date' => 'Leaving Date',

            'id_card_status' => 'ID Card Status',
            'dress_status' => 'Dress Status',
            'police_verification_status' => 'Police Verification Status',
            'police_certificate_number' => 'Police Certificate Number',

            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ] as $field => $label)

            <tr>
                <th style="width: 35%; background-color: #f8f9fa;">
                    {{ $label }}
                </th>

                <td id="view_employee_{{ $field }}">
                    —
                </td>
            </tr>

        @endforeach
    </tbody>
</table>








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

    // New Code Added


const policeSwitch = document.getElementById(
    'police_verification_status'
);

const policeCertificateWrapper = document.getElementById(
    'police_certificate_wrapper'
);

const policeCertificateInput = document.getElementById(
    'police_certificate_number'
);

function togglePoliceCertificate() {
    const isVerified = policeSwitch.checked;

    policeCertificateWrapper.classList.toggle(
        'd-none',
        !isVerified
    );

    // Require the certificate number only when verified.
    policeCertificateInput.required = isVerified;

    // Clear the value when verification is switched off.
    if (!isVerified) {
        policeCertificateInput.value = '';
        policeCertificateInput.classList.remove('is-invalid');

        const feedback = policeCertificateInput
            .parentElement.querySelector('.invalid-feedback');

        if (feedback) {
            feedback.textContent = '';
        }
    }
}

// Show/hide when the user changes the switch.
policeSwitch.addEventListener(
    'change',
    togglePoliceCertificate
);

// Initial state.
togglePoliceCertificate();



    // New Code Added

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
        togglePoliceCertificate();
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
        'id', 
        'employee_code', 
        'first_name', 
        'last_name', 
        'email', 
        'phone', 
        'work_location',
        'department', 
        'designation',
        'aadhar_number',
        'driving_license_number',
        'account_number',
        'ifsc_code',
        'uan_number',
        'esi_number',
        'joining_date',
        'leaving_date',
        'id_card_status',
        'dress_status',
        'police_verification_status',
        'police_certificate_number',
        'status',
        'created_at', 
        'updated_at'
    ];






    Object.entries(employee).forEach(([key, value]) => {
                    const td = document.getElementById(`view_employee_${key}`);
                    // if (field) field.value = value;

                    if (key === 'created_at' || key === 'updated_at') {
                        value = formatDate(value);
                    }
                    if (key === 'id_card_status' || key === 'dress_status' || key === 'police_verification_status') {
                        

                        if (value === '1') {
                            value = 'Issued';
                        } else if (value === '0') {
                            value = 'Not Issued';
                        } else {
                            value = '—';
                        }
                    }


                    if (td) {
                        td.textContent = td.value = value || '—';
                    }
                });

    // fields.forEach(function (field) {
    //     // const input = document.getElementById('view_employee_' + field);
    //     // if (input) input.value = employee[field] || '—';

    //     // if (td) input.textContent = employee[field] || '—';


    //     const td = document.getElementById('view_employee_' + field);

    //     if (td) {
    //         td.textContent = employee[field] || '—';
    //     }




    // });

    // Object.entries(employee).forEach(([field, value]) => {

    //     if (field === 'created_at' || field === 'updated_at') {
    //         alert("Hello");
    //         alert(value);
    //         value = formatDate(value);
    //     }

    //     $('#view_employee_' + field).val(value ?? '');

    // });

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

            // Object.keys(employee).forEach(function (key) {
            //     const field = form.querySelector('[name="' + key + '"]');

            //     if (field && employee[key] !== null) {
            //         field.value = employee[key];
            //     }
            // });


            Object.keys(employee).forEach(function (key) {
                const field = form.querySelector('[name="' + key + '"]');

                if (!field) return;

                if (field.type === 'checkbox') {
                    field.checked = Number(employee[key]) === 1;
                } else {
                    field.value = employee[key] ?? '';
                }
            });

            // Update the certificate field based on the saved switch.
            togglePoliceCertificate();


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










function formatDate(dateString) {
    
    if (!dateString) return '';

    const date = new Date(dateString);

    if (isNaN(date.getTime())) return '';

    const day = date.getDate();

    const suffix =
        day % 10 === 1 && day !== 11 ? 'st' :
        day % 10 === 2 && day !== 12 ? 'nd' :
        day % 10 === 3 && day !== 13 ? 'rd' : 'th';

    const month = date.toLocaleString('en-GB', {
        month: 'long'
    });

    const year = date.getFullYear();

    // alert(`${day}${suffix} ${month} ${year}`);

    return `${day}${suffix} ${month} ${year}`;
}





document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('employeeFilterForm');
    const tableContent = document.getElementById('employeeTableContent');
    const loader = document.getElementById('employeeTableLoader');








    const resetButton = document.getElementById('resetEmployeeFilters');

if (resetButton) {
    resetButton.addEventListener('click', function (event) {
        event.preventDefault();

        filterForm.reset();

        const url = new URL(filterForm.action, window.location.origin);

        loadEmployees(url.toString());
    });
}

    if (!filterForm || !tableContent || !loader) {
        return;
    }

    let activeController = null;

    async function loadEmployees(url) {
        // Cancel the previous filter request if it is still running.
        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        loader.classList.remove('d-none');

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: activeController.signal
            });

            if (!response.ok) {
                throw new Error('Unable to load employees.');
            }

            const html = await response.text();

            // Replace only the table, not the whole page.
            tableContent.innerHTML = html;

        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Employee filter error:', error);
                alert('Unable to load employees. Please try again.');
            }
        } finally {
            loader.classList.add('d-none');
            activeController = null;
        }
    }

    // Search and filter without reloading the page.
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const url = new URL(filterForm.action, window.location.origin);
        const params = new URLSearchParams(new FormData(filterForm));

        url.search = params.toString();

        loadEmployees(url.toString());
    });

    // Handle pagination clicks through AJAX as well.
    tableContent.addEventListener('click', function (event) {
        const link = event.target.closest('.pagination a');

        if (!link) {
            return;
        }

        event.preventDefault();

        loadEmployees(link.href);
    });
});


</script>
@endsection
