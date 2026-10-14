
<div class="modal fade" id="employeeModal" tabindex="-1"
     aria-labelledby="employeeModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="employeeForm" method="POST" action="{{ route('employees.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">
                        Add Employee
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="formErrors"
                         class="alert alert-danger d-none"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Employee Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="employee_code"
                                   class="form-control" required
                                   maxlength="50">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="first_name"
                                   class="form-control" required
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name"
                                   class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email"
                                   class="form-control" maxlength="150">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone"
                                   class="form-control" maxlength="20">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department"
                                   class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation"
                                   class="form-control" maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Leaving Date</label>
                            <input type="date" name="leaving_date"
                                   class="form-control">
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
