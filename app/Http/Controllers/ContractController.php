<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('contracts as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employee_id')
            ->select('c.*', 'e.employee_code', 'e.first_name', 'e.last_name');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($query) use ($search) {
                $query->where('c.contract_number', 'like', "%{$search}%")
                    ->orWhere('c.title', 'like', "%{$search}%")
                    ->orWhere('e.employee_code', 'like', "%{$search}%")
                    ->orWhere('e.first_name', 'like', "%{$search}%")
                    ->orWhere('e.last_name', 'like', "%{$search}%")
                    ->orWhere('c.department', 'like', "%{$search}%")
                    ->orWhere('c.organization', 'like', "%{$search}%")
                    ->orWhere('c.contract_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('c.status', $request->input('status'));
        }

        $contracts = $query->orderByDesc('c.id')->paginate(10)->withQueryString();
        return view('contracts.index', compact('contracts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedContractData($request);
        $now = now();

        DB::table('contracts')->insert([
            ...$validated,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json(['message' => 'Contract added successfully.']);
    }

    public function update(Request $request, int $id)
    {
        abort_unless(DB::table('contracts')->where('id', $id)->exists(), 404, 'Contract not found.');

        DB::table('contracts')->where('id', $id)->update([
            ...$this->validatedContractData($request, $id),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Contract updated successfully.']);
    }

    public function destroy(int $id)
    {
        $deleted = DB::table('contracts')->where('id', $id)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Contract not found.'], 404);
        }

        return response()->json(['message' => 'Contract deleted successfully.']);
    }

    private function validatedContractData(Request $request, ?int $contractId = null): array
    {
        return $request->validate([
            'contract_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('contracts', 'contract_number')->ignore($contractId),
            ],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:100'],
            'organization' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:5000'],
            'contract_type' => ['required', Rule::in(['Manpower', 'Ambulance', 'Cabs'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', Rule::in(['draft', 'active', 'completed', 'terminated'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
