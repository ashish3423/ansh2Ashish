
<div class="card position-relative" id="employeeTableCard">

    {{-- Loading overlay --}}
    <div id="employeeTableLoader"
         class="employee-table-loader d-none"
         aria-live="polite"
         aria-label="Loading employees">
        <div class="text-center">
            <div class="spinner-border text-primary"
                 role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 fw-semibold">Loading employees...</div>
        </div>
    </div>

    <div id="employeeTableContent">
        @include('employees.partials.table-content', ['employees' => $employees])
    </div>

</div>
