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

        @can('records.create')
            <button type="button" class="btn btn-primary" id="addSalaryBtn">
                <i class="bi bi-plus-circle"></i> Add Salary
            </button>
        @endcan

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

<div class="col-sm-6 col-lg-3">
    <label for="filter_month" class="form-label">Month</label>
    <select name="month" id="filter_month" class="form-select">
        <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>All Months</option>
        @foreach(range(1, 12) as $month)
            <option value="{{ $month }}"
                {{ (string) $selectedMonth === (string) $month ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(2000, $month, 1)->format('F') }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-sm-6 col-lg-3">
    <label for="filter_year" class="form-label">Year</label>
    <select name="year" id="filter_year" class="form-select">
        <option value="" {{ request()->has('year') && request('year') === '' ? 'selected' : '' }}>All Years</option>
        @foreach($salaryYears as $year)
            <option value="{{ $year }}"
                {{ (string) request('year', now()->year) === (string) $year ? 'selected' : '' }}>
                {{ $year }}
            </option>
        @endforeach
    </select>
</div>




                    @foreach(['total_salary' => 'Total Salary', 'bonus_amount' => 'Bonus', 'gross_salary' => 'Gross Total', 'deductions' => 'Deductions', 'net_salary' => 'Net Salary'] as $field => $label)
                        <div class="col-sm-6 col-lg-3">
                            <label for="filter_{{ $field }}" class="form-label">{{ $label }}</label>
                            <input type="number" min="0" step="0.01" name="{{ $field }}"
                                   id="filter_{{ $field }}" class="form-control" value="{{ request($field) }}">
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

        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Salary Records</h5>
            <a href="{{ route('employee-salaries.export', request()->query()) }}"
                   id="exportSalaryExcelBtn" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>

        <div class="card-body">
            <p class="small text-muted mb-3">
                Select salary rows to export only those records. Leave all rows unselected to export the full list matching your filters.
            </p>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>
                                <input type="checkbox" class="form-check-input" id="selectAllSalaryRows"
                                       aria-label="Select all salary rows on this page">
                            </th>
                            <th>ID</th><th>Employee</th><th>Salary Period</th><th>Contract Number</th>
                            <th>Daily Wages</th><th>Working Days</th><th>Total Salary</th>
                            <th>Bonus</th><th>EPF Employee</th><th>EPF Employer</th>
                            <th>ESI Employee</th><th>ESI Employer</th><th>Gross Total</th>
                            <th>Deductions</th><th>Net Salary</th><th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($salaries as $salary)

                            <tr>

                                <td>
                                    <input type="checkbox" class="form-check-input salary-export-checkbox"
                                           value="{{ $salary->id }}"
                                           aria-label="Select salary record for {{ $salary->employee_code }} {{ $salary->first_name }}">
                                </td>
                                <td>{{ $salary->id }}</td>

                                <td>{{ $salary->employee_code }} — {{ $salary->first_name }} {{ $salary->last_name }}</td>
                                <td>{{ $salary->effective_from ? \Carbon\Carbon::parse($salary->effective_from)->format('F Y') : '-' }}</td>
                                <td>{{ $salary->contract_number ?: '-' }}</td>
                                <td>{{ $salary->daily_wage !== null ? number_format((float) $salary->daily_wage, 2) : '-' }}</td>
                                <td>{{ number_format((float) $salary->working_days, 2) }}</td>
                                <td>{{ number_format((float) $salary->total_salary, 2) }}</td>
                                <td>{{ number_format((float) $salary->bonus_amount, 2) }}</td>
                                <td>{{ number_format((float) $salary->epf_employee_contribution, 2) }}</td>
                                <td>{{ number_format((float) $salary->epf_employer_contribution, 2) }}</td>
                                <td>{{ number_format((float) $salary->esi_employee_contribution, 2) }}</td>
                                <td>{{ number_format((float) $salary->esi_employer_contribution, 2) }}</td>
                                <td>{{ number_format((float) $salary->gross_salary, 2) }}</td>
                                <td>{{ number_format((float) $salary->deductions, 2) }}</td>
                                <td>{{ number_format((float) $salary->net_salary, 2) }}</td>

<td class="salary-actions">

    @can('records.update')

    <button type="button"

        class="btn btn-link salary-edit-icon edit-salary-btn"

        title="Edit Salary"

        aria-label="Edit Salary"

        data-id="{{ $salary->id }}"

        data-salary="{{ rawurlencode(json_encode($salary)) }}">

    <i class="bi bi-pencil-square"></i>

</button>
    @endcan



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

            @can('records.delete')
            <li>

                <button type="button"

                        class="dropdown-item text-danger delete-salary-btn"

                        data-id="{{ $salary->id }}"
                        data-name="{{ $salary->employee_code }} — {{ $salary->first_name }} {{ $salary->last_name }}">

                    <i class="bi bi-trash3 me-2"></i>

                    Delete

                </button>

            </li>
            @endcan

        </ul>

    </div>

</td>



                            </tr>

                        @empty

                            <tr><td colspan="17" class="text-center py-4">No salary records found.</td></tr>

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
                                            data-daily-wage="{{ $employee->wages_rate_per_day }}"
                                            data-emp-type="{{ $employee->emp_type }}"
                                            data-designation="{{ $employee->designation }}"
                                            data-work-location="{{ $employee->work_location }}"
                                            data-contract-number="{{ $contractsByEmployee->get($employee->id)->contract_number ?? '' }}"
                                            data-paid-holidays="{{ $contractsByEmployee->get($employee->id)->paid_holidays ?? 0 }}"
                                            data-paid-holidays-percentage="{{ $contractsByEmployee->get($employee->id)->paid_holidays_percentage ?? '' }}"
                                            data-leave-with-wages="{{ $contractsByEmployee->get($employee->id)->leave_with_wages ?? 0 }}"
                                            data-leave-with-wages-percentage="{{ $contractsByEmployee->get($employee->id)->leave_with_wages_percentage ?? '' }}">
                                        {{ $employee->employee_code }} — {{ $employee->first_name }} {{ $employee->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_month" class="form-label">Month <span class="text-danger">*</span></label>
                            <select name="salary_month" id="salary_month" class="form-select" required>
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" {{ now()->month === $month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(2000, $month, 1)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_year" class="form-label">Year <span class="text-danger">*</span></label>
                            <select name="salary_year" id="salary_year" class="form-select" required>
                                @foreach($salaryYears as $year)
                                    <option value="{{ $year }}" {{ now()->year === $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_contract_number" class="form-label">Contract Number</label>
                            <input type="text" id="salary_contract_number" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_daily_wage" class="form-label">Daily Wages</label>
                            <input type="text" id="salary_daily_wage" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_employee_category" class="form-label">Employee Category</label>
                            <input type="text" id="salary_employee_category" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_designation" class="form-label">Designation</label>
                            <input type="text" id="salary_designation" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_work_location" class="form-label">Work Location</label>
                            <input type="text" id="salary_work_location" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_working_days" class="form-label">Working Days</label>
                            <input type="number" name="working_days" id="salary_working_days"
                                   class="form-control" min="0" max="31" step="0.01" value="0" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                        </div>

                        <div class="col-md-6">
                            <label for="salary_total_salary" class="form-label">Total Salary (Days × Daily Wage)</label>
                            <input type="number" id="salary_total_salary" class="form-control" value="0.00" readonly>
                        </div>

                        <div class="col-md-6 d-none" id="salaryPaidHolidaysField">
                            <label for="salary_paid_holidays_amount" class="form-label">PH Amount</label>
                            <input type="number" id="salary_paid_holidays_amount" class="form-control" value="0.00" readonly>
                        </div>

                        <div class="col-md-6 d-none" id="salaryLeaveWithWagesField">
                            <label for="salary_leave_with_wages_amount" class="form-label">LWW Amount</label>
                            <input type="number" id="salary_leave_with_wages_amount" class="form-control" value="0.00" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="salary_bonus_amount" class="form-label">Bonus Amount Given</label>
                            <input type="number" name="bonus_amount" id="salary_bonus_amount"
                                   class="form-control" min="0" step="0.01" value="0">
                            <div class="form-text">Enabled only when total salary is less than the bonus eligibility amount.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        @foreach([
                            'epf_employee_contribution' => 'EPF Employee Share',
                            'epf_employer_contribution' => 'EPF Employer Share',
                            'esi_employee_contribution' => 'ESI Employee Share',
                            'esi_employer_contribution' => 'ESI Employer Share',
                            'gross_salary' => 'Gross Total',
                            'deductions' => 'Deductions (Employee EPF + ESI)',
                            'net_salary' => 'Net Salary',
                        ] as $field => $label)
                            <div class="col-md-6">
                                <label for="salary_{{ $field }}" class="form-label">{{ $label }}</label>
                                <input type="number" id="salary_{{ $field }}" class="form-control" value="0.00" readonly>
                            </div>
                        @endforeach

                    </div>

                    <small class="text-muted d-block mt-3">
                        Gross total = total salary + bonus + employer EPF/ESI shares. Net salary = gross total minus employer shares and employee deductions.
                    </small>

                    <div class="alert alert-info py-2 mt-3 mb-0" id="complianceEligibilityStatus">
                        Select an employee and enter working days to calculate eligibility and contributions.
                    </div>

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
            
            'employee' => 'Employee',
            'contract_number' => 'Contract Number',
            'daily_wage' => 'Daily Wages',
            'working_days' => 'Working Days',
            'total_salary' => 'Total Salary',
            'bonus_amount' => 'Bonus Amount',
            'epf_employee_contribution' => 'EPF Employee Share',
            'epf_employer_contribution' => 'EPF Employer Share',
            'esi_employee_contribution' => 'ESI Employee Share',
            'esi_employer_contribution' => 'ESI Employer Share',
            'gross_salary' => 'Gross Total',
            'deductions' => 'Deductions',
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
    const complianceSettings = @json($complianceSettings);
    const workingDaysInput = document.getElementById('salary_working_days');
    const bonusInput = document.getElementById('salary_bonus_amount');
    let deleteId = null;
    const exportButton = document.getElementById('exportSalaryExcelBtn');
    const selectAllSalaryRows = document.getElementById('selectAllSalaryRows');
    const salaryExportCheckboxes = Array.from(document.querySelectorAll('.salary-export-checkbox'));

    function updateSalarySelection() {
        const selectedCount = salaryExportCheckboxes.filter(checkbox => checkbox.checked).length;
        selectAllSalaryRows.checked = salaryExportCheckboxes.length > 0 &&
            selectedCount === salaryExportCheckboxes.length;
        selectAllSalaryRows.indeterminate = selectedCount > 0 &&
            selectedCount < salaryExportCheckboxes.length;
    }

    selectAllSalaryRows?.addEventListener('change', () => {
        salaryExportCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllSalaryRows.checked;
        });
    });

    salaryExportCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSalarySelection);
    });

    exportButton?.addEventListener('click', event => {
        const selectedIds = salaryExportCheckboxes
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);
        if (selectedIds.length === 0) return;

        event.preventDefault();
        const url = new URL(exportButton.href);
        selectedIds.forEach(id => url.searchParams.append('selected_ids[]', id));
        window.location.href = url.toString();
    });

    function updateEmployeeDetails() {
        const employee = employeeSelect.selectedOptions[0];
        const employeeTypes = {
            '0': 'Unskilled',
            '1': 'Skilled',
            '2': 'Semi-Skilled',
            '3': 'Highly Skilled'
        };

        document.getElementById('salary_daily_wage').value = employee?.dataset.dailyWage || '';
        document.getElementById('salary_employee_category').value =
            employeeTypes[employee?.dataset.empType] || '';
        document.getElementById('salary_designation').value = employee?.dataset.designation || '';
        document.getElementById('salary_work_location').value = employee?.dataset.workLocation || '';
        document.getElementById('salary_contract_number').value = employee?.dataset.contractNumber || '';
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(feedback => feedback.textContent = '');
        formAlert.textContent = '';
        formAlert.classList.add('d-none');
    }

    function recalculate() {
        const workingDays = Number(workingDaysInput.value || 0);
        const dailyWage = Number(employeeSelect.selectedOptions[0]?.dataset.dailyWage || 0);
        const totalSalary = Math.round(
            workingDays * dailyWage * 100
        ) / 100;
        const selectedEmployee = employeeSelect.selectedOptions[0];
        const hasPaidHolidays = selectedEmployee?.dataset.paidHolidays === '1';
        const hasLeaveWithWages = selectedEmployee?.dataset.leaveWithWages === '1';
        const paidHolidaysPercentage = Number(selectedEmployee?.dataset.paidHolidaysPercentage || 0);
        const leaveWithWagesPercentage = Number(selectedEmployee?.dataset.leaveWithWagesPercentage || 0);
        const paidHolidays = hasPaidHolidays
            ? Math.round(totalSalary * paidHolidaysPercentage) / 100 : 0;
        const leaveWithWages = hasLeaveWithWages
            ? Math.round(totalSalary * leaveWithWagesPercentage) / 100 : 0;
        const epfThreshold = complianceSettings?.epf_eligibility_amount;
        const esiThreshold = complianceSettings?.esi_eligibility_amount;
        const bonusThreshold = complianceSettings?.bonus_eligibility_amount;
        const epfEligible = epfThreshold !== null && epfThreshold !== undefined &&
            totalSalary >= Number(epfThreshold);
        const esiEligible = esiThreshold !== null && esiThreshold !== undefined &&
            totalSalary <= Number(esiThreshold);
        const bonusEligible = bonusThreshold !== null && bonusThreshold !== undefined &&
            totalSalary < Number(bonusThreshold);
        const epfEmployee = epfEligible
            ? Math.round(Number(epfThreshold) * Number(complianceSettings?.epf_employee_percentage || 0) / 100) : 0;
        const epfEmployer = epfEligible
            ? Math.round(Number(epfThreshold) * Number(complianceSettings?.epf_employer_percentage || 0) / 100) : 0;
        const esiEmployee = esiEligible
            ? Math.round(totalSalary * Number(complianceSettings?.esi_employee_percentage || 0) / 100) : 0;
        const esiEmployer = esiEligible
            ? Math.round(totalSalary * Number(complianceSettings?.esi_employer_percentage || 0) / 100) : 0;
        document.getElementById('salary_total_salary').value = totalSalary.toFixed(2);
        document.getElementById('salary_paid_holidays_amount').value = paidHolidays.toFixed(2);
        document.getElementById('salary_leave_with_wages_amount').value = leaveWithWages.toFixed(2);
        document.getElementById('salaryPaidHolidaysField').classList.toggle('d-none', !hasPaidHolidays);
        document.getElementById('salaryLeaveWithWagesField').classList.toggle('d-none', !hasLeaveWithWages);
        document.getElementById('complianceEligibilityStatus').textContent =
            `EPF: ${epfEligible ? 'Eligible' : 'Not eligible'}; ESI: ${esiEligible ? 'Eligible' : 'Not eligible'}; Bonus: ${bonusEligible ? 'Eligible' : 'Not eligible'}.`;
        bonusInput.disabled = !bonusEligible;
        if (!bonusEligible) bonusInput.value = '0';
        const bonus = Number(bonusInput.value || 0);
        const deductions = epfEmployee + esiEmployee;
        const gross = totalSalary + paidHolidays + leaveWithWages + bonus + epfEmployer + esiEmployer;
        const net = gross - epfEmployer - esiEmployer - deductions;
        [
            ['epf_employee_contribution', epfEmployee],
            ['epf_employer_contribution', epfEmployer],
            ['esi_employee_contribution', esiEmployee],
            ['esi_employer_contribution', esiEmployer],
            ['gross_salary', gross],
            ['deductions', deductions],
            ['net_salary', net],
        ].forEach(([field, amount]) => {
            document.getElementById(`salary_${field}`).value = Number(amount).toFixed(2);
        });
    }

    bonusInput.addEventListener('input', recalculate);
    workingDaysInput.addEventListener('input', recalculate);
    employeeSelect.addEventListener('change', () => {
        updateEmployeeDetails();
        recalculate();
    });

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
                    //id: salary.id ?? '—',
                    //employee_id: salary.employee_id ?? '—',
                    employee: `${salary.employee_code || ''}${salary.employee_code ? ' — ' : ''}${employeeName}`.trim() || '—',
                    contract_number: salary.contract_number || '—',
                    daily_wage: salary.daily_wage !== null && salary.daily_wage !== undefined
                        ? Number(salary.daily_wage).toFixed(2) : '—',
                    working_days: Number(salary.working_days || 0).toFixed(2),
                    total_salary: Number(salary.total_salary || 0).toFixed(2),
                    bonus_amount: Number(salary.bonus_amount || 0).toFixed(2),
                    epf_employee_contribution: Number(salary.epf_employee_contribution || 0).toFixed(2),
                    epf_employer_contribution: Number(salary.epf_employer_contribution || 0).toFixed(2),
                    esi_employee_contribution: Number(salary.esi_employee_contribution || 0).toFixed(2),
                    esi_employer_contribution: Number(salary.esi_employer_contribution || 0).toFixed(2),
                    gross_salary: Number(salary.gross_salary || 0).toFixed(2),
                    deductions: Number(salary.deductions || 0).toFixed(2),
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
                if (salary.effective_from) {
                    const [year, month] = salary.effective_from.split('-');
                    form.elements.namedItem('salary_month').value = Number(month);
                    form.elements.namedItem('salary_year').value = Number(year);
                }
                updateEmployeeDetails();
                recalculate();
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
