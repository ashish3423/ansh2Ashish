<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeSalaryController extends Controller
{
    private const FILTERABLE_AMOUNT_FIELDS = [
        'basic_salary', 'hra', 'allowances', 'deductions', 'gross_salary', 'net_salary',
    ];

    public function index(Request $request)
    {
        // $query = DB::table('employee_salaries')
        //     ->join('employees', 'employees.id', '=', 'employee_salaries.employee_id')
        //     ->select('employee_salaries.*', 'employees.employee_code', 'employees.first_name', 'employees.last_name');

        // foreach (['id', 'employee_id'] as $field) {
        //     if ($request->filled($field)) {
        //         $query->where("employee_salaries.{$field}", $request->input($field));
        //     }
        // }

        // foreach (self::FILTERABLE_AMOUNT_FIELDS as $field) {
        //     if ($request->filled($field)) {
        //         $query->where("employee_salaries.{$field}", $request->input($field));
        //     }
        // }

        // $salaries = $query->orderByDesc('employee_salaries.id')
        //     ->paginate(10)
        //     ->withQueryString();

        // $employees = DB::table('employees')
        //     ->select('id', 'employee_code', 'first_name', 'last_name')
        //     ->orderBy('first_name')
        //     ->orderBy('last_name')
        //     ->get();

        // return view('employee-salaries.index', compact('salaries', 'employees'));


            $query = DB::table('employee_salaries as s')
        ->join('employees as e', 'e.id', '=', 's.employee_id')
        ->select(
            's.*',
            'e.employee_code',
            'e.first_name',
            'e.last_name'
        );

    // Filter by Salary ID
    if ($request->filled('id')) {
        $query->where('s.id', $request->id);
    }

    // Filter by selected Employee
    if ($request->filled('employee_id')) {
        $query->where('s.employee_id', $request->employee_id);
    }

    // Numeric filters
    foreach ([
        'basic_salary',
        'hra',
        'allowances',
        'deductions',
        'gross_salary',
        'net_salary'
    ] as $field) {
        if ($request->filled($field)) {
            $query->where("s.$field", $request->input($field));
        }
    }

    $salaries = $query
        ->orderByDesc('s.id')
        ->paginate(10)
        ->withQueryString();

    // Unique employees for the dropdown
    $employees = DB::table('employees')
        ->select('id', 'employee_code', 'first_name', 'last_name', 'designation')
        ->orderBy('employee_code')
        ->get();

    return view('employee-salaries.index', compact(
        'salaries',
        'employees'
    ));
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
            'basic_salary' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'hra' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'allowances' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'deductions' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ]);

        $basic = (float) $validated['basic_salary'];
        $hra = (float) ($validated['hra'] ?? 0);
        $allowances = (float) ($validated['allowances'] ?? 0);
        $deductions = (float) ($validated['deductions'] ?? 0);
        $gross = round($basic + $hra + $allowances, 2);

        return [
            'employee_id' => $validated['employee_id'],
            'basic_salary' => $basic,
            'hra' => $hra,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'gross_salary' => $gross,
            'net_salary' => round($gross - $deductions, 2),
            'effective_from' => $existingSalary->effective_from ?? now()->toDateString(),
            'effective_to' => $existingSalary->effective_to ?? null,
        ];
    }
}
