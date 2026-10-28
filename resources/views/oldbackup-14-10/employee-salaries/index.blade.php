@extends('layouts.newapp')

@section('title', 'Employee Salaries')
@section('breadcrumb', 'Employee Salaries')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-1">Employee Salaries</h3>
            <p class="text-muted mb-0">Manage salary details and effective dates</p>
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
                    @foreach(['id' => 'Salary ID', 'employee_id' => 'Employee ID', 'basic_salary' => 'Basic Salary', 'hra' => 'HRA', 'allowances' => 'Allowances', 'deductions' => 'Deductions', 'gross_salary' => 'Gross Salary', 'net_salary' => 'Net Salary'] as $field => $label)
                        <div class="col-sm-6 col-lg-3">
                            <label for="filter_{{ $field }}" class="form-label">{{ $label }}</label>
                            <input type="number" @if(str_contains($field, 'salary') || in_array($field, ['hra', 'allowances', 'deductions'])) step="0.01" min="0" @else min="1" step="1" @endif
                                   name="{{ $field }}" id="filter_{{ $field }}" class="form-control"
                                   value="{{ request($field) }}">
                        </div>
                    @endforeach
                    @foreach(['effective_from' => 'Effective From', 'effective_to' => 'Effective To'] as $field => $label)
                        <div class="col-sm-6 col-lg-3">
                            <label for="filter_{{ $field }}" class="form-label">{{ $label }}</label>
                            <input type="date" name="{{ $field }}" id="filter_{{ $field }}" class="form-control" value="{{ request($field) }}">
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
                            <th>Deductions</th><th>Gross</th><th>Net</th><th>Effective From</th><th>Effective To</th><th>Actions</th>
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
                                <td>{{ \Carbon\Carbon::parse($salary->effective_from)->format('d-m-Y') }}</td>
                                <td>{{ $salary->effective_to ? \Carbon\Carbon::parse($salary->effective_to)->format('d-m-Y') : '—' }}</td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-info view-salary-btn"
                                    data-salary="{{ rawurlencode(json_encode($salary)) }}">View</button>
                                    <button type="button" class="btn btn-sm btn-warning edit-salary-btn"
                                            data-salary="{{ rawurlencode(json_encode($salary)) }}">Edit</button>
                                    <button type="button" class="btn btn-sm btn-danger delete-salary-btn"
                                            data-id="{{ $salary->id }}"
                                            data-name="{{ $salary->employee_code }} — {{ $salary->first_name }} {{ $salary->last_name }}">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center py-4">No salary records found.</td></tr>
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
                                    <option value="{{ $employee->id }}">{{ $employee->employee_code }} — {{ $employee->first_name }} {{ $employee->last_name }}</option>
                                @endforeach
                            </select><div class="invalid-feedback"></div>
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
                        <div class="col-md-6">
                            <label for="salary_effective_from" class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="date" name="effective_from" id="salary_effective_from" class="form-control" required><div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="salary_effective_to" class="form-label">Effective To</label>
                            <input type="date" name="effective_to" id="salary_effective_to" class="form-control"><div class="invalid-feedback"></div>
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
                <div class="row g-3">
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
                        'effective_from' => 'Effective From',
                        'effective_to' => 'Effective To',
                        'created_at' => 'Created At',
                        'updated_at' => 'Updated At',
                    ] as $field => $label)
                        <div class="col-md-6">
                            <label class="form-label" for="view_salary_{{ $field }}">{{ $label }}</label>
                            <input type="text" class="form-control"
                                   id="view_salary_{{ $field }}" disabled>
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
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('salaryForm');
    const salaryModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('salaryModal'));
    const deleteModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteSalaryModal'));

    const viewModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById('viewSalaryModal')
);
    const baseUrl = @json(url('/employee-salaries'));
    const indexUrl = @json(route('employee-salaries.index'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const saveButton = document.getElementById('saveSalaryBtn');
    const formAlert = document.getElementById('salaryFormAlert');
    let deleteId = null;

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(feedback => feedback.textContent = '');
        formAlert.textContent = '';
        formAlert.classList.add('d-none');
    }

    function recalculate() {
        const value = name => Number(form.elements[name].value || 0);
        const gross = value('basic_salary') + value('hra') + value('allowances');
        document.getElementById('salary_gross_salary').value = gross.toFixed(2);
        document.getElementById('salary_net_salary').value = (gross - value('deductions')).toFixed(2);
    }

    form.querySelectorAll('.amount-input').forEach(input => input.addEventListener('input', recalculate));

    document.getElementById('addSalaryBtn').addEventListener('click', () => {
        form.reset(); clearErrors(); recalculate();
        form.action = baseUrl;
        document.getElementById('salaryMethod').value = 'POST';
        document.getElementById('salaryModalLabel').textContent = 'Add Employee Salary';
        saveButton.textContent = 'Save Salary';
        salaryModal.show();
    });

    document.addEventListener('click', event => {





const view = event.target.closest('.view-salary-btn');
if (view) {
    const salary = JSON.parse(decodeURIComponent(view.dataset.salary));
    const values = {
        id: salary.id,
        employee_id: salary.employee_id,
        employee: `${salary.employee_code} — ${salary.first_name} ${salary.last_name || ''}`.trim(),
        basic_salary: Number(salary.basic_salary || 0).toFixed(2),
        hra: Number(salary.hra || 0).toFixed(2),
        allowances: Number(salary.allowances || 0).toFixed(2),
        deductions: Number(salary.deductions || 0).toFixed(3),
        gross_salary: Number(salary.gross_salary || 0).toFixed(2),
        net_salary: Number(salary.net_salary || 0).toFixed(2),
        effective_from: salary.effective_from || '—',
        effective_to: salary.effective_to || '—',
        created_at: salary.created_at || '—',
        updated_at: salary.updated_at || '—'
    };

    Object.entries(values).forEach(([key, value]) => {
        document.getElementById(`view_salary_${key}`).value = value;
    });

    viewModal.show();
    return;
}





        const edit = event.target.closest('.edit-salary-btn');
        if (edit) {
            const salary = JSON.parse(decodeURIComponent(edit.dataset.salary));
            form.reset(); clearErrors();
            Object.entries(salary).forEach(([key, value]) => {
                if (form.elements[key] && value !== null) form.elements[key].value = value;
            });
            form.action = `${baseUrl}/${salary.id}`;
            document.getElementById('salaryMethod').value = 'PUT';
            document.getElementById('salaryModalLabel').textContent = 'Edit Employee Salary';
            saveButton.textContent = 'Update Salary';
            recalculate(); salaryModal.show();
            return;
        }

        const remove = event.target.closest('.delete-salary-btn');
        if (remove) {
            deleteId = remove.dataset.id;
            document.getElementById('deleteSalaryName').textContent = remove.dataset.name;
            deleteModal.show();
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault(); clearErrors();
        saveButton.disabled = true; saveButton.textContent = 'Saving...';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf},
                body: new FormData(form)
            });
            const data = await response.json();
            if (response.status === 422 && data.errors) {
                const messages = [];
                Object.entries(data.errors).forEach(([name, errors]) => {
                    messages.push(...errors);
                    const field = form.elements[name];
                    if (field) {
                        field.classList.add('is-invalid');
                        const feedback = field.parentElement.querySelector('.invalid-feedback');
                        if (feedback) feedback.textContent = errors[0];
                    }
                });
                formAlert.textContent = messages.join(' '); formAlert.classList.remove('d-none');
                return;
            }
            if (!response.ok) throw new Error(data.message || 'Unable to save salary record.');
            window.location.href = indexUrl;
        } catch (error) {
            formAlert.textContent = error.message || 'Unable to save salary record.';
            formAlert.classList.remove('d-none');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = document.getElementById('salaryMethod').value === 'PUT' ? 'Update Salary Now' : 'Save Salary Now';
        }
    });

    document.getElementById('confirmDeleteSalaryBtn').addEventListener('click', async event => {
        const button = event.currentTarget; button.disabled = true; button.textContent = 'Deleting...';
        try {
            const response = await fetch(`${baseUrl}/${deleteId}`, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
                body: new URLSearchParams({_method: 'DELETE', _token: csrf})
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Unable to delete salary record.');
            window.location.href = indexUrl;
        } catch (error) {
            window.alert(error.message || 'Unable to delete salary record.');
        } finally {
            button.disabled = false; button.textContent = 'Yes, Delete';
        }
    });
});
</script>
@endsection
