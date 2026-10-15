
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
                    <th style="min-width:150px">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td>{{ $employees->firstItem() + $loop->index }}</td>
                        <td>{{ $employee->employee_code }}</td>
                        <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
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

                        <td class="employee-actions">
                            <button type="button"
                                    class="btn btn-link edit-icon-btn edit-employee-btn"
                                    title="Edit Employee"
                                    aria-label="Edit Employee"
                                    data-employee="{{ rawurlencode(json_encode($employee)) }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <div class="dropdown d-inline-block">
                                <button type="button"
                                        class="btn btn-link more-actions-btn"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        aria-label="More actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end employee-action-menu">
                                    <li>
                                        <button type="button"
                                                class="dropdown-item view-employee-btn"
                                                data-employee="{{ rawurlencode(json_encode($employee)) }}">
                                            <i class="bi bi-eye me-2"></i>
                                            View Details
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button"
                                                class="dropdown-item text-danger delete-employee-btn"
                                                data-id="{{ $employee->id }}"
                                                data-name="{{ $employee->first_name }} {{ $employee->last_name }}">
                                            <i class="bi bi-trash3 me-2"></i>
                                            Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
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
        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Showing {{ $employees->firstItem() }} to
                {{ $employees->lastItem() }} of
                {{ $employees->total() }} employees
            </small>

            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
