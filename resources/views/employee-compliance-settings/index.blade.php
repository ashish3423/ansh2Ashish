@extends('layouts.newapp')

@section('title', 'EPF, ESI & Bonus Settings')
@section('breadcrumb', 'EPF, ESI & Bonus Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h3 class="mb-1">EPF, ESI &amp; Bonus Settings</h3>
        <p class="text-muted mb-0">Configure contribution percentages and eligibility amounts.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @can('manage-compliance-settings')
                <form method="POST" action="{{ route('employee-compliance-settings.update') }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    @foreach ([
                        'epf_employee_percentage' => 'EPF Employee Share (%)',
                        'epf_employer_percentage' => 'EPF Employer Share (%)',
                        'esi_employee_percentage' => 'ESI Employee Share (%)',
                        'esi_employer_percentage' => 'ESI Employer Share (%)',
                        'epf_eligibility_amount' => 'EPF Eligibility Amount',
                        'bonus_eligibility_amount' => 'Bonus Eligibility Amount',
                    ] as $field => $label)
                        <div class="col-md-6">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            <input
                                class="form-control @error($field) is-invalid @enderror"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                type="number"
                                min="0"
                                @if (str_ends_with($field, 'percentage')) max="100" @endif
                                step="0.01"
                                value="{{ old($field, $settings->$field) }}"
                                required
                            >
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                    <div class="col-12">
                        <p class="small text-muted mb-2">
                            Eligibility salary is calculated as the employee's daily wage rate multiplied by working days entered on their salary record.
                            EPF applies when this amount is greater than or equal to its threshold; bonus applies only when it is greater than its threshold.
                            Both contribution shares are calculated from this amount. ESI shares are calculated for every salary record.
                        </p>
                        <button class="btn btn-primary" type="submit">Save Settings</button>
                    </div>
                </form>
            @else
                <dl class="row mb-0">
                    @foreach ([
                        'epf_employee_percentage' => 'EPF Employee Share (%)',
                        'epf_employer_percentage' => 'EPF Employer Share (%)',
                        'esi_employee_percentage' => 'ESI Employee Share (%)',
                        'esi_employer_percentage' => 'ESI Employer Share (%)',
                        'epf_eligibility_amount' => 'EPF Eligibility Amount',
                        'bonus_eligibility_amount' => 'Bonus Eligibility Amount',
                    ] as $field => $label)
                        <dt class="col-md-6">{{ $label }}</dt>
                        <dd class="col-md-6">{{ $settings->$field ?? 'Not configured' }}</dd>
                    @endforeach
                </dl>
                <p class="small text-muted mb-0">
                    Eligibility salary is daily wage rate multiplied by working days. EPF requires salary greater than or equal to its threshold; bonus requires salary greater than its threshold.
                </p>
            @endcan
        </div>
    </div>
</div>
@endsection
