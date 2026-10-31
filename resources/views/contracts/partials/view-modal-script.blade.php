const viewContractModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById('viewContractModal')
);
document.addEventListener('click', function (event) {
    const button = event.target.closest('.view-contract-btn');
    if (!button) return;

    const contract = JSON.parse(decodeURIComponent(button.dataset.contract));
    const dateFields = ['start_date', 'end_date'];
    const booleanFields = ['paid_holidays', 'leave_with_wages', 'bonus_enabled'];

    Object.entries(contract).forEach(([field, value]) => {
        const cell = document.getElementById('view_contract_' + field);
        if (!cell) return;

        if (dateFields.includes(field) && value) {
            const [year, month, day] = value.slice(0, 10).split('-');
            value = `${day}-${month}-${year}`;
        } else if (booleanFields.includes(field)) {
            const percentage = contract[field + '_percentage'];
            value = Number(value) === 1
                ? 'Yes' + (percentage !== null && percentage !== '' ? ` (${percentage}%)` : '')
                : 'No';
        } else if (field === 'amount' && value !== null && value !== '') {
            value = Number(value).toFixed(2);
        } else if (field === 'contract_category') {
            value = value === 'cash_purchase_direct' ? 'Cash Purchase (Direct)' : 'Regular Contract';
        } else if (field === 'status' && value) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
        }

        cell.textContent = value === null || value === '' ? '—' : value;
    });

    const assignedServicesList = document.getElementById('view_contract_assigned_services');
    const noAssignedServices = document.getElementById('view_contract_no_assigned_services');
    assignedServicesList.replaceChildren();
    (contract.assigned_services || []).forEach(service => {
        const item = document.createElement('li');
        item.textContent = service;
        assignedServicesList.appendChild(item);
    });
    noAssignedServices.classList.toggle('d-none', assignedServicesList.childElementCount > 0);

    document.getElementById('viewContractModalLabel').textContent =
        'Contract Details — ' + contract.contract_number;
    viewContractModal.show();
});
