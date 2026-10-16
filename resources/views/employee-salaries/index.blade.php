@extends('layouts.newapp')



@section('title', 'Employee Salaries')

@section('breadcrumb', 'Employee Salaries')



@section('content')

<div class="container-fluid py-4 salary-management-page">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>

            <h4 class="mb-1">Employee Salaries</h4>

            <p class="text-muted mb-0">Manage Salary details</p>

        </div>

        <button type="button" class="btn btn-primary" id="addSalaryBtn">

            <i class="bi bi-plus-circle"></i> Add Salary

        </button>

    </div>



    <div id="pageAlert" class="alert d-none" role="alert"></div>



    <div class="card mb-4">

        <div class="card-header bg-white"><h5 class="mb-0">Filters</h5></div>

        <div class="card-body">

            <form method="GET" action="{{ route('employee-salaries.index') }}">

                <div class="row g-3 align-items-end">

<style>
/* Employee dropdown */
#filter_employee_id {
    max-height: 150px;
    overflow-y: auto;
}

/* Employee dropdown options */
#filter_employee_id option {
    padding: 8px;
}

/* Scrollbar styling */
#filter_employee_id::-webkit-scrollbar {
    width: 8px;
}

#filter_employee_id::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#filter_employee_id::-webkit-scrollbar-thumb {
    background: #999;
    border-radius: 5px;
}

#filter_employee_id::-webkit-scrollbar-thumb:hover {
    background: #666;
}
</style>
<div class="col-sm-6 col-lg-3">
    <label for="filter_employee_id" class="form-label">
        Employee
    </label>

    <select name="employee_id"
            id="filter_employee_id"
            class="form-select">

        <option value="">All Employees</option>

        @foreach($employees as $employee)
            <option value="{{ $employee->id }}"
                {{ request('employee_id') == $employee->id ? 'selected' : '' }}>

                {{ $employee->employee_code }} —
                {{ $employee->first_name }}
                {{ $employee->last_name }}
            </option>
        @endforeach

    </select>
</div>







                    @foreach(['basic_salary' => 'Basic Salary', 'deductions' => 'Deductions', 'gross_salary' => 'Gross Salary', 'net_salary' => 'Net Salary'] as $field => $label)


                    








                        <div class="col-sm-6 col-lg-3">

                            <label for="filter_{{ $field }}" class="form-label">{{ $label }}</label>

                            <input type="number" @if(str_contains($field, 'salary') || in_array($field, ['hra', 'allowances', 'deductions'])) step="0.01" min="0" @else min="1" step="1" @endif

                                   name="{{ $field }}" id="filter_{{ $field }}" class="form-control"

                                   value="{{ request($field) }}">

                        </div>

                    @endforeach

                    <div class="col-sm-6 col-lg-3 d-flex gap-2">

                        <button type="submit" class="btn btn-primary">Filter</button>

                        <a href="{{ route('employee-salaries.index') }}" class="btn btn-secondary">Reset</a>

                    </div>

                </div>

            </form>

        </div>

    </div>



    <div class="card">

        <div class="card-header bg-white"><h5 class="mb-0">Salary Records</h5></div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th><th>Employee</th><th>Basic</th><th>HRA</th><th>Allowances</th>

                            <th>Deductions</th><th>Gross</th><th>Net</th><th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($salaries as $salary)

                            <tr>

                                <td>{{ $salary->id }}</td>

                                <td>{{ $salary->employee_code }} — {{ $salary->first_name }} {{ $salary->last_name }}</td>

                                <td>{{ number_format((float) $salary->basic_salary, 2) }}</td>

                                <td>{{ number_format((float) $salary->hra, 2) }}</td>

                                <td>{{ number_format((float) $salary->allowances, 2) }}</td>

                                <td>{{ number_format((float) $salary->deductions, 2) }}</td>

                                <td>{{ number_format((float) $salary->gross_salary, 2) }}</td>

                                <td>{{ number_format((float) $salary->net_salary, 2) }}</td>

<td class="salary-actions">

    <!-- Edit icon -->

    <button type="button"

        class="btn btn-link salary-edit-icon edit-salary-btn"

        title="Edit Salary"

        aria-label="Edit Salary"

        data-id="{{ $salary->id }}"

        data-salary="{{ rawurlencode(json_encode($salary)) }}">

    <i class="bi bi-pencil-square"></i>

</button>



    <!-- Three dots menu -->

<div class="dropdown dropup d-inline-block">

        <button type="button"

                class="btn btn-link salary-more-btn"

                data-bs-toggle="dropdown"

                aria-expanded="false"

                aria-label="More actions">

            <i class="bi bi-three-dots-vertical"></i>

        </button>



        <ul class="dropdown-menu dropdown-menu-end salary-action-menu">

            <li>

                <button type="button"

                        class="dropdown-item view-salary-btn"

                        data-id="{{ $salary->id }}"
                        data-salary="{{ rawurlencode(json_encode($salary)) }}">

                    <i class="bi bi-eye me-2"></i>

                    View Details

                </button>

            </li>

            <li>

                <button type="button"

                        class="dropdown-item text-danger delete-salary-btn"

                        data-id="{{ $salary->id }}"
                        data-name="{{ $salary->employee_code }} — {{ $salary->first_name }} {{ $salary->last_name }}">

                    <i class="bi bi-trash3 me-2"></i>

                    Delete

                </button>

            </li>

        </ul>

    </div>

</td>



                            </tr>

                        @empty

                            <tr><td colspan="9" class="text-center py-4">No salary records found.</td></tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            @if($salaries->hasPages())

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">

                    <small class="text-muted">Showing {{ $salaries->firstItem() }} to {{ $salaries->lastItem() }} of {{ $salaries->total() }} salary records</small>

                    {{ $salaries->links('pagination::bootstrap-5') }}

                </div>

            @endif

        </div>

    </div>

</div>





<style>
#salaryModal .modal-dialog {
    max-height: calc(100vh - 2rem) !important;
    margin-top: 1rem !important;
    margin-bottom: 1rem !important;
}

#salaryModal .modal-content {
    max-height: calc(100vh - 2rem) !important;
    overflow: hidden !important;
}

#salaryModal #salaryForm {
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
    overflow: hidden !important;
}

#salaryModal .modal-body {
    min-height: 0 !important;
    max-height: calc(100vh - 200px) !important;
    overflow-y: scroll !important;
    overflow-x: hidden !important;
    flex: 1 1 auto !important;
}
</style>







<div class="modal fade" id="salaryModal" tabindex="-1" aria-labelledby="salaryModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <form id="salaryForm" method="POST" action="{{ route('employee-salaries.store') }}">

                @csrf

                <input type="hidden" id="salaryMethod" name="_method" value="POST">

                <div class="modal-header">

                    <h5 class="modal-title" id="salaryModalLabel">Add Employee Salary</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>

                <div class="modal-body">

                    <div id="salaryFormAlert" class="alert alert-danger d-none"></div>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label for="salary_employee_id" class="form-label">Employee <span class="text-danger">*</span></label>

                            <select name="employee_id" id="salary_employee_id" class="form-select" required>

                                <option value="">Select employee</option>

                                @foreach($employees as $employee)

                                    <option value="{{ $employee->id }}"
                                            data-designation="{{ $employee->designation }}"
                                            data-department="{{ $employee->department }}"
                                            data-wages-rate-per-day="{{ $employee->wages_rate_per_day }}"
                                            data-emp-type="{{ $employee->emp_type }}">
                                        {{ $employee->employee_code }} — {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>

                                @endforeach

                            </select><div class="invalid-feedback"></div>

                        </div>

                        <div class="col-md-6">

                            <label for="salary_designation" class="form-label">Designation</label>

                            <input type="text" id="salary_designation" class="form-control" readonly>

                        </div>

                        <div class="col-md-6">

                            <label for="salary_department" class="form-label">Department</label>

                            <input type="text" id="salary_department" class="form-control" readonly>

                        </div>

                        <div class="col-md-6">

                            <label for="salary_wages_rate_per_day" class="form-label">Daily Wages Rate (<i class="bi bi-currency-rupee"></i>)</label>

                            <input type="text" id="salary_wages_rate_per_day" class="form-control" readonly>

                        </div>

                        <div class="col-md-6">

                            <label for="salary_emp_type" class="form-label">Employee Category</label>

                            <input type="text" id="salary_emp_type" class="form-control" readonly>

                        </div>

                        @foreach(['basic_salary' => 'Basic Salary', 'hra' => 'HRA', 'allowances' => 'Allowances', 'deductions' => 'Deductions'] as $field => $label)

                            <div class="col-md-6">

                                <label for="salary_{{ $field }}" class="form-label">{{ $label }} @if($field === 'basic_salary')<span class="text-danger">*</span>@endif</label>

                                <input type="number" name="{{ $field }}" id="salary_{{ $field }}" class="form-control amount-input" min="0" step="0.01" {{ $field === 'basic_salary' ? 'required' : '' }} value="0">

                                <div class="invalid-feedback"></div>

                            </div>

                        @endforeach

                        <div class="col-md-6">

                            <label for="salary_gross_salary" class="form-label">Gross Salary</label>

                            <input type="number" id="salary_gross_salary" class="form-control" value="0.00" readonly>

                        </div>

                        <div class="col-md-6">

                            <label for="salary_net_salary" class="form-label">Net Salary</label>

                            <input type="number" id="salary_net_salary" class="form-control" value="0.00" readonly>

                        </div>

                    </div>

                    <small class="text-muted d-block mt-3">Gross salary is calculated as basic + HRA + allowances; net salary is gross minus deductions.</small>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn btn-primary" id="saveSalaryBtn">Save Salary</button>

                </div>

            </form>

        </div>

    </div>

</div>



<div class="modal fade" id="deleteSalaryModal" tabindex="-1" aria-labelledby="deleteSalaryModalLabel" aria-hidden="true">

    <div class="modal-dialog"><div class="modal-content">

        <div class="modal-header">

            <h5 class="modal-title" id="deleteSalaryModalLabel">Confirm Delete</h5>

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

        </div>

        <div class="modal-body">Delete the salary record for <strong id="deleteSalaryName"></strong>?</div>

        <div class="modal-footer">

            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

            <button type="button" class="btn btn-danger" id="confirmDeleteSalaryBtn">Yes, Delete</button>

        </div>

    </div></div>

</div>







<div class="modal fade" id="viewSalaryModal" tabindex="-1"

     aria-labelledby="viewSalaryModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="viewSalaryModalLabel">Salary Record Details</h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"

                        aria-label="Close"></button>

            </div>

            <div class="modal-body">

                
<table class="table table-bordered table-hover align-middle">
    <tbody>
        @foreach([
            'id' => 'Salary ID',
            'employee_id' => 'Employee ID',
            'employee' => 'Employee',
            'basic_salary' => 'Basic Salary',
            'hra' => 'HRA',
            'allowances' => 'Allowances',
            'deductions' => 'Deductions',
            'gross_salary' => 'Gross Salary',
            'net_salary' => 'Net Salary',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ] as $field => $label)

            <tr>
                <th style="width: 35%; background-color: #f8f9fa;">
                    {{ $label }}
                </th>

                <td id="view_salary_{{ $field }}">
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
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('salaryForm');
    const salaryModalElement = document.getElementById('salaryModal');
    const deleteModalElement = document.getElementById('deleteSalaryModal');
    const viewModalElement = document.getElementById('viewSalaryModal');

    if (!form || !salaryModalElement || !deleteModalElement || !viewModalElement || !window.bootstrap) {
        console.error('Salary page initialization failed: required modal/form or Bootstrap is missing.');
        return;
    }

    const salaryModal = bootstrap.Modal.getOrCreateInstance(salaryModalElement);
    const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalElement);
    const viewModal = bootstrap.Modal.getOrCreateInstance(viewModalElement);
    const baseUrl = @json(url('/employee-salaries'));
    const indexUrl = @json(route('employee-salaries.index'));
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.content : (form.querySelector('input[name="_token"]')?.value || '');
    const saveButton = document.getElementById('saveSalaryBtn');
    const formAlert = document.getElementById('salaryFormAlert');
    const employeeSelect = document.getElementById('salary_employee_id');
    let deleteId = null;

    function updateEmployeeDetails() {
        const employee = employeeSelect.selectedOptions[0];
        const employeeTypes = {
            '0': 'Unskilled',
            '1': 'Skilled',
            '2': 'Semi-Skilled',
            '3': 'Highly Skilled'
        };

        document.getElementById('salary_designation').value = employee?.dataset.designation || '';
        document.getElementById('salary_department').value = employee?.dataset.department || '';
        document.getElementById('salary_wages_rate_per_day').value =
            employee?.dataset.wagesRatePerDay || '';
        document.getElementById('salary_emp_type').value =
            employeeTypes[employee?.dataset.empType] || '';
    }

    employeeSelect.addEventListener('change', updateEmployeeDetails);

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(feedback => feedback.textContent = '');
        formAlert.textContent = '';
        formAlert.classList.add('d-none');
    }

    function recalculate() {
        const value = name => Number(form.elements[name]?.value || 0);
        const gross = value('basic_salary') + value('hra') + value('allowances');
        const grossField = document.getElementById('salary_gross_salary');
        const netField = document.getElementById('salary_net_salary');
        if (grossField) grossField.value = gross.toFixed(2);
        if (netField) netField.value = (gross - value('deductions')).toFixed(2);
    }

    form.querySelectorAll('.amount-input').forEach(input => input.addEventListener('input', recalculate));

    document.getElementById('addSalaryBtn')?.addEventListener('click', () => {
        form.reset();
        clearErrors();
        updateEmployeeDetails();
        recalculate();
        form.action = baseUrl;
        document.getElementById('salaryMethod').value = 'POST';
        document.getElementById('salaryModalLabel').textContent = 'Add Employee Salary';
        saveButton.textContent = 'Save Salary';
        salaryModal.show();
    });

    // A single delegated handler manages View, Edit, and Delete actions.
    document.addEventListener('click', event => {
        const viewButton = event.target.closest('.view-salary-btn');
        if (viewButton) {
            try {
                const raw = viewButton.dataset.salary;
                if (!raw) throw new Error('Salary data is missing from the View Details button.');
                const salary = JSON.parse(decodeURIComponent(raw));
                const employeeName = [salary.first_name, salary.last_name].filter(Boolean).join(' ');
                const values = {
                    id: salary.id ?? '—',
                    employee_id: salary.employee_id ?? '—',
                    employee: `${salary.employee_code || ''}${salary.employee_code ? ' — ' : ''}${employeeName}`.trim() || '—',
                    basic_salary: Number(salary.basic_salary || 0).toFixed(2),
                    hra: Number(salary.hra || 0).toFixed(2),
                    allowances: Number(salary.allowances || 0).toFixed(2),
                    deductions: Number(salary.deductions || 0).toFixed(2),
                    gross_salary: Number(salary.gross_salary || 0).toFixed(2),
                    net_salary: Number(salary.net_salary || 0).toFixed(2),
                    created_at: salary.created_at || '—',
                    updated_at: salary.updated_at || '—'
                };
                Object.entries(values).forEach(([key, value]) => {
                    const td = document.getElementById(`view_salary_${key}`);
                    // if (field) field.value = value;

                    if (key === 'created_at' || key === 'updated_at') {
            value = formatDate(value);
        }


                    if (td) {
                        td.textContent = td.value = value || '—';
                    }
                });
                viewModal.show();
            } catch (error) {
                console.error('View Salary error:', error);
                window.alert('Unable to open salary details. Please check the browser console.');
            }
            return;
        }

        const editButton = event.target.closest('.edit-salary-btn, .salary-edit-icon');
        if (editButton) {
            try {
                const raw = editButton.dataset.salary;
                if (!raw) throw new Error('Salary data is missing from the Edit button.');
                const salary = JSON.parse(decodeURIComponent(raw));
                form.reset();
                clearErrors();
                Object.entries(salary).forEach(([key, value]) => {
                    const field = form.elements.namedItem(key);
                    if (field && value !== null && value !== undefined && 'value' in field) {
                        field.value = value;
                    }
                });
                updateEmployeeDetails();
                form.action = `${baseUrl}/${salary.id}`;
                document.getElementById('salaryMethod').value = 'PUT';
                document.getElementById('salaryModalLabel').textContent = 'Edit Employee Salary';
                saveButton.textContent = 'Update Salary';
                recalculate();
                salaryModal.show();
            } catch (error) {
                console.error('Edit Salary error:', error);
                window.alert('Unable to open the edit popup. Please check the browser console.');
            }
            return;
        }

        const deleteButton = event.target.closest('.delete-salary-btn');
        if (deleteButton) {
            deleteId = deleteButton.dataset.id;
            const nameElement = document.getElementById('deleteSalaryName');
            if (nameElement) nameElement.textContent = deleteButton.dataset.name || 'this employee';
            deleteModal.show();
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearErrors();
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: new FormData(form)
            });
            const data = await response.json();
            if (response.status === 422 && data.errors) {
                const messages = [];
                Object.entries(data.errors).forEach(([name, errors]) => {
                    messages.push(...errors);
                    const field = form.elements.namedItem(name);
                    if (field && 'classList' in field) {
                        field.classList.add('is-invalid');
                        const feedback = field.parentElement.querySelector('.invalid-feedback');
                        if (feedback) feedback.textContent = errors[0];
                    }
                });
                formAlert.textContent = messages.join(' ');
                formAlert.classList.remove('d-none');
                return;
            }
            if (!response.ok) throw new Error(data.message || 'Unable to save salary record.');
            window.location.href = indexUrl;
        } catch (error) {
            formAlert.textContent = error.message || 'Unable to save salary record.';
            formAlert.classList.remove('d-none');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = document.getElementById('salaryMethod').value === 'PUT' ? 'Update Salary' : 'Save Salary';
        }
    });

    document.getElementById('confirmDeleteSalaryBtn')?.addEventListener('click', async event => {
        if (!deleteId) return;
        const button = event.currentTarget;
        button.disabled = true;
        button.textContent = 'Deleting...';
        try {
            const response = await fetch(`${baseUrl}/${deleteId}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: new URLSearchParams({ _method: 'DELETE', _token: csrf })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to delete salary record.');
            window.location.href = indexUrl;
        } catch (error) {
            window.alert(error.message || 'Unable to delete salary record.');
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

    return `${day}${suffix} ${month} ${year}`;
}







</script>
@endsection
