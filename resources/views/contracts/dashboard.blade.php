@extends('layouts.newapp')

@section('title', 'Contract Dashboard')
@section('breadcrumb', 'Contract Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Contract Dashboard</h4>
            <p class="text-muted mb-0">Overview of contract status, categories, and upcoming expirations.</p>
        </div>
        <a href="{{ route('contracts.index') }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-text"></i> Manage Contracts
        </a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Total Contracts', $totalContracts, 'bi-files', 'primary'],
            ['Active Contracts', $activeContracts, 'bi-check-circle', 'success'],
            ['Draft Contracts', $draftContracts, 'bi-pencil-square', 'secondary'],
            ['Completed Contracts', $completedContracts, 'bi-clipboard-check', 'info'],
            ['Terminated Contracts', $terminatedContracts, 'bi-x-circle', 'danger'],
        ] as [$label, $count, $icon, $color])
            <div class="col-sm-6 col-xl">
                <div class="card h-100 border-start border-4 border-{{ $color }}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fs-4 fw-semibold">{{ $count }}</div>
                        </div>
                        <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white"><h5 class="mb-0">Contracts by Category</h5></div>
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Cash Purchase (Direct)</div>
                        <div class="fs-4 fw-semibold">{{ $cashPurchaseContracts }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Regular Contract</div>
                        <div class="fs-4 fw-semibold">{{ $regularContracts }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Expiring in the Next 30 Days</h5>
                    <span class="badge bg-warning text-dark">{{ $expiringContracts->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Contract</th><th>Organization</th><th>End Date</th></tr>
                            </thead>
                            <tbody>
                                @forelse($expiringContracts as $contract)
                                    <tr>
                                        <td>{{ $contract->contract_number }}</td>
                                        <td>{{ $contract->organization ?: '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($contract->end_date)->format('d-m-Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No active contracts expiring soon.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><h5 class="mb-0">Recent Contracts</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contract Number</th>
                            <th>Organization</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentContracts as $contract)
                            <tr>
                                <td>{{ $contract->contract_number }}</td>
                                <td>{{ $contract->organization ?: '-' }}</td>
                                <td>{{ $contract->contract_type ?: '-' }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($contract->status) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($contract->start_date)->format('d-m-Y') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary view-contract-btn"
                                            data-contract="{{ rawurlencode(json_encode($contract)) }}">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No contracts to display.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('contracts.partials.view-modal')
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @include('contracts.partials.view-modal-script')
});
</script>
@endsection
