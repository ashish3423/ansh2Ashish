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
                            'contract_category' => 'Contract Category',
                            'organization' => 'Organization',
                            'department' => 'Department',
                            'address' => 'Address',
                            'start_date' => 'Start Date',
                            'end_date' => 'End Date',
                            'amount' => 'Contract Amount',
                            'paid_holidays' => 'Paid Holidays (PH)',
                            'leave_with_wages' => 'Leave with Wages (LWW)',
                            'bonus_enabled' => 'Bonus',
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
