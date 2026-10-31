<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\SalaryExport;

class EmployeeSalaryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => ['sometimes', 'nullable', 'in:all,1,2,3,4,5,6,7,8,9,10,11,12'],
            'year' => ['sometimes', 'nullable', 'integer', 'between:1900,2200'],
        ]);

        $salaries = $this->filteredSalaryQuery($request)
            ->paginate(10)
            ->withQueryString();
        $employees = DB::table('employees')
            ->select(
                'id',
                'employee_code',
                'first_name',
                'last_name',
                'designation',
                'work_location',
                'wages_rate_per_day',
                'emp_type',
                'epf_enabled',
                'esi_enabled'
            )
            ->orderBy('employee_code')
            ->get();
        $contractsByEmployee = $this->contractsByEmployee($employees->pluck('id')->all());
        $salaries->getCollection()->transform(function (object $salary) use ($contractsByEmployee) {
            $contract = $contractsByEmployee->get($salary->employee_id);
            $salary->contract_number = $contract->contract_number ?? null;

            return $salary;
        });
        $complianceSettings = DB::table('employee_compliance_settings')->where('id', 1)->first();
        $selectedMonth = $request->input('month') ?: 'all';
        $salaryYears = collect(range(now()->year + 1, now()->year - 10))
            ->merge(DB::table('employee_salaries')
                ->whereNotNull('effective_from')
                ->pluck('effective_from')
                ->map(fn ($date): int => (int) date('Y', strtotime($date))))
            ->unique()
            ->sortDesc()
            ->values();

        return view('employee-salaries.index', compact(
            'salaries',
            'employees',
            'contractsByEmployee',
            'complianceSettings',
            'salaryYears',
            'selectedMonth'
        ));
    }

    public function export(Request $request, SalaryExport $exporter)
    {
        $validated = $request->validate([
            'selected_ids' => ['sometimes', 'array'],
            'selected_ids.*' => ['integer', 'distinct', 'exists:employee_salaries,id'],
            'month' => ['sometimes', 'nullable', 'in:all,1,2,3,4,5,6,7,8,9,10,11,12'],
            'year' => ['sometimes', 'nullable', 'integer', 'between:1900,2200'],
        ]);

        $query = $this->filteredSalaryQuery($request);
        if (! empty($validated['selected_ids'])) {
            $query->whereIn('s.id', $validated['selected_ids']);
        }

        $salaries = $query->get();
        $contractsByEmployee = $this->contractsByEmployee($salaries->pluck('employee_id')->all());
        $rows = $salaries->map(function (object $salary) use ($contractsByEmployee): array {
            $contract = $contractsByEmployee->get($salary->employee_id);

            return [
                $salary->id,
                trim($salary->employee_code . ' — ' . $salary->first_name . ' ' . ($salary->last_name ?? '')),
                $salary->effective_from ? date('F', strtotime($salary->effective_from)) : '-',
                $salary->effective_from ? (int) date('Y', strtotime($salary->effective_from)) : '-',
                $salary->designation ?? '-',
                match ($salary->emp_type === null ? null : (int) $salary->emp_type) {
                    0 => 'Unskilled',
                    1 => 'Skilled',
                    2 => 'Semi-Skilled',
                    3 => 'Highly Skilled',
                    default => '-',
                },
                $contract->contract_number ?? '-',
                $salary->daily_wage === null ? '-' : (float) $salary->daily_wage,
                (float) $salary->working_days,
                (float) $salary->total_salary,
                (float) $salary->bonus_amount + (float) $salary->contract_bonus_amount,
                (bool) $salary->epf_enabled ? (float) $salary->epf_employee_contribution : '-',
                (bool) $salary->epf_enabled ? (float) $salary->epf_employer_contribution : '-',
                (bool) $salary->esi_enabled ? (float) $salary->esi_employee_contribution : '-',
                (bool) $salary->esi_enabled ? (float) $salary->esi_employer_contribution : '-',
                (float) $salary->gross_salary,
                (float) $salary->deductions,
                (float) $salary->net_salary,
            ];
        })->all();

        $headings = [
            'ID', 'Employee', 'Salary Month', 'Salary Year', 'Designation', 'Employee Category', 'Contract Number', 'Daily Wages',
            'Working Days', 'Total Salary', 'Bonus', 'EPF Employee',
            'EPF Employer', 'ESI Employee', 'ESI Employer', 'Gross Total',
            'Deductions', 'Net Salary',
        ];

        return $exporter->downloadXlsx($headings, $rows);
    }

    public function store(Request $request)
    {
        $data = $this->validatedSalaryData($request);
        $now = now();

        DB::table('employee_salaries')->insert([
            ...$data,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(['message' => 'Employee salary added successfully.']);
    }

    public function update(Request $request, $id)
    {
        $salary = DB::table('employee_salaries')
            ->select('effective_from', 'effective_to')
            ->where('id', $id)
            ->first();
        abort_unless($salary, 404, 'Salary record not found.');

        DB::table('employee_salaries')->where('id', $id)->update([
            ...$this->validatedSalaryData($request, $salary),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Employee salary updated successfully.']);
    }

    public function destroy($id)
    {
        $deleted = DB::table('employee_salaries')->where('id', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Salary record not found.'], 404);
        }

        return response()->json(['message' => 'Employee salary deleted successfully.']);
    }

    private function validatedSalaryData(Request $request, ?object $existingSalary = null): array
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'working_days' => ['required', 'numeric', 'min:0', 'max:31', 'decimal:0,2'],
            'bonus_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'salary_month' => ['required', 'integer', 'between:1,12'],
            'salary_year' => ['required', 'integer', 'between:1900,2200'],
        ]);

        $employee = DB::table('employees')
            ->select('wages_rate_per_day', 'epf_enabled', 'esi_enabled')
            ->where('id', $validated['employee_id'])
            ->first();
        abort_unless($employee, 404, 'Employee not found.');

        $settings = DB::table('employee_compliance_settings')->where('id', 1)->first();
        $workingDays = (float) $validated['working_days'];
        $totalSalary = round($workingDays * (float) ($employee->wages_rate_per_day ?? 0), 2);
        $contract = $this->contractsByEmployee([(int) $validated['employee_id']])
            ->get($validated['employee_id']);
        $contractBonus = ! empty($contract?->bonus_enabled)
            ? round($totalSalary * (float) ($contract->bonus_percentage ?? 0) / 100, 2)
            : 0;
        $paidHolidays = ! empty($contract?->paid_holidays)
            ? round($totalSalary * (float) ($contract->paid_holidays_percentage ?? 0) / 100, 2)
            : 0;
        $leaveWithWages = ! empty($contract?->leave_with_wages)
            ? round($totalSalary * (float) ($contract->leave_with_wages_percentage ?? 0) / 100, 2)
            : 0;
        $epfEligible = (bool) $employee->epf_enabled
            && $settings
            && $settings->epf_eligibility_amount !== null
            && $totalSalary >= (float) $settings->epf_eligibility_amount;
        $esiEligible = (bool) $employee->esi_enabled
            && $settings
            && $settings->esi_eligibility_amount !== null
            && $totalSalary <= (float) $settings->esi_eligibility_amount;
        $bonusEligible = $settings
            && $settings->bonus_eligibility_amount !== null
            && $totalSalary < (float) $settings->bonus_eligibility_amount;
        $bonus = (float) ($validated['bonus_amount'] ?? 0);

        if ($bonus > 0 && ! $bonusEligible) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'bonus_amount' => 'This employee does not meet the bonus eligibility threshold.',
            ]);
        }

        $epfEmployee = $epfEligible
            ? round((float) $settings->epf_eligibility_amount * (float) ($settings->epf_employee_percentage ?? 0) / 100)
            : 0;
        $epfEmployer = $epfEligible
            ? round((float) $settings->epf_eligibility_amount * (float) ($settings->epf_employer_percentage ?? 0) / 100)
            : 0;
        $esiEmployee = $esiEligible
            ? round($totalSalary * (float) ($settings->esi_employee_percentage ?? 0) / 100)
            : 0;
        $esiEmployer = $esiEligible
            ? round($totalSalary * (float) ($settings->esi_employer_percentage ?? 0) / 100)
            : 0;
        $deductions = round($epfEmployee + $esiEmployee, 2);
        $gross = round($totalSalary + $paidHolidays + $leaveWithWages + $bonus + $contractBonus + $epfEmployer + $esiEmployer, 2);
        $net = round($gross - $epfEmployer - $esiEmployer - $deductions, 2);

        return [
            'employee_id' => $validated['employee_id'],
            'working_days' => $workingDays,
            'eligibility_salary' => $totalSalary,
            'total_salary' => $totalSalary,
            'basic_salary' => 0,
            'hra' => 0,
            'allowances' => 0,
            'deductions' => $deductions,
            'bonus_amount' => $bonus,
            'contract_bonus_amount' => $contractBonus,
            'epf_employee_contribution' => $epfEmployee,
            'epf_employer_contribution' => $epfEmployer,
            'esi_employee_contribution' => $esiEmployee,
            'esi_employer_contribution' => $esiEmployer,
            'gross_salary' => $gross,
            'net_salary' => $net,
            'effective_from' => sprintf('%04d-%02d-01', $validated['salary_year'], $validated['salary_month']),
            'effective_to' => $existingSalary->effective_to ?? null,
        ];
    }

    private function filteredSalaryQuery(Request $request)
    {
        $query = DB::table('employee_salaries as s')
            ->join('employees as e', 'e.id', '=', 's.employee_id')
            ->select(
                's.*',
                'e.employee_code',
                'e.first_name',
                'e.last_name',
                'e.designation',
                'e.emp_type',
                'e.epf_enabled',
                'e.esi_enabled',
                'e.wages_rate_per_day as daily_wage'
            );

        if ($request->filled('employee_id')) {
            $query->where('s.employee_id', $request->input('employee_id'));
        }

        $month = $request->input('month');
        if ($month && $month !== 'all') {
            $query->whereMonth('s.effective_from', (int) $month);
        }

        if ($request->has('year')) {
            if ($request->filled('year')) {
                $query->whereYear('s.effective_from', (int) $request->input('year'));
            }
        } else {
            $query->whereYear('s.effective_from', now()->year);
        }

        foreach (['total_salary', 'bonus_amount', 'deductions', 'gross_salary', 'net_salary'] as $field) {
            if ($request->filled($field)) {
                $query->where("s.{$field}", $request->input($field));
            }
        }

        return $query->orderByDesc('s.id');
    }

    private function contractsByEmployee(array $employeeIds): \Illuminate\Support\Collection
    {
        $contracts = DB::table('contracts')
            ->whereIn('employee_id', $employeeIds)
            ->select(
                'employee_id',
                'contract_number',
                'status',
                'id',
                'paid_holidays',
                'paid_holidays_percentage',
                'leave_with_wages',
                'leave_with_wages_percentage',
                'contract_category',
                'bonus_enabled',
                'bonus_percentage'
            )
            ->get();
        $assignedContracts = DB::table('contract_service_assignments as a')
            ->join('contracts as c', 'c.id', '=', 'a.contract_id')
            ->where(function ($query) use ($employeeIds): void {
                foreach (['employee_id', 'driver_id', 'assistant_id'] as $field) {
                    $query->orWhereIn("a.{$field}", $employeeIds);
                }
            })
            ->select(
                'a.employee_id',
                'a.driver_id',
                'a.assistant_id',
                'c.contract_number',
                'c.status',
                'c.id',
                'c.paid_holidays',
                'c.paid_holidays_percentage',
                'c.leave_with_wages',
                'c.leave_with_wages_percentage',
                'c.contract_category',
                'c.bonus_enabled',
                'c.bonus_percentage'
            )
            ->get()
            ->flatMap(function (object $contract): array {
                return collect([
                    $contract->employee_id,
                    $contract->driver_id,
                    $contract->assistant_id,
                ])
                    ->filter()
                    ->map(fn ($employeeId): object => (object) [
                        'employee_id' => $employeeId,
                        'contract_number' => $contract->contract_number,
                        'status' => $contract->status,
                        'id' => $contract->id,
                        'paid_holidays' => $contract->paid_holidays,
                        'paid_holidays_percentage' => $contract->paid_holidays_percentage,
                        'leave_with_wages' => $contract->leave_with_wages,
                        'leave_with_wages_percentage' => $contract->leave_with_wages_percentage,
                        'contract_category' => $contract->contract_category,
                        'bonus_enabled' => $contract->bonus_enabled,
                        'bonus_percentage' => $contract->bonus_percentage,
                    ])
                    ->all();
            });

        return $contracts->concat($assignedContracts)
            ->sort(function (object $left, object $right): int {
                $statusOrder = ($left->status !== 'active') <=> ($right->status !== 'active');

                return $statusOrder ?: ($right->id <=> $left->id);
            })
            ->unique('employee_id')
            ->keyBy('employee_id');
    }
}
