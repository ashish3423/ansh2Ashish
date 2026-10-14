
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1"
     aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">
                    Delete Employee
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete
                <strong id="deleteEmployeeName"></strong>?
                <p class="text-danger mb-0 mt-2">
                    This action cannot be undone.
                </p>
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
