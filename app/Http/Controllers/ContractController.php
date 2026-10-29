<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('contracts as c');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($query) use ($search) {
                $query->where('c.contract_number', 'like', "%{$search}%")
                    ->orWhere('c.department', 'like', "%{$search}%")
                    ->orWhere('c.organization', 'like', "%{$search}%")
                    ->orWhere('c.contract_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('c.status', $request->input('status'));
        }

        $contracts = $query->orderByDesc('c.id')->paginate(10)->withQueryString();
        $assignmentsByContract = DB::table('contract_service_assignments as a')
            ->leftJoin('employees as e', 'e.id', '=', 'a.employee_id')
            ->leftJoin('service_vehicles as v', 'v.id', '=', 'a.service_vehicle_id')
            ->leftJoin('employees as d', 'd.id', '=', 'a.driver_id')
            ->leftJoin('employees as assistant', 'assistant.id', '=', 'a.assistant_id')
            ->whereIn('a.contract_id', $contracts->getCollection()->pluck('id'))
            ->select(
                'a.contract_id',
                'a.employee_id',
                'a.service_vehicle_id',
                'a.driver_id',
                'a.assistant_id',
                'e.employee_code as employee_code',
                'e.first_name as employee_first_name',
                'e.last_name as employee_last_name',
                'v.registration_number',
                'd.employee_code as driver_code',
                'd.first_name as driver_first_name',
                'd.last_name as driver_last_name',
                'assistant.employee_code as assistant_code',
                'assistant.first_name as assistant_first_name',
                'assistant.last_name as assistant_last_name'
            )
            ->get()
            ->groupBy('contract_id');

        $contracts->getCollection()->each(function (object $contract) use ($assignmentsByContract): void {
            $contract->assigned_services = $assignmentsByContract
                ->get($contract->id, collect())
                ->flatMap(function (object $assignment): array {
                    $resources = [];

                    if ($assignment->employee_id) {
                        $resources[] = 'Employee: ' . $assignment->employee_code . ' — '
                            . trim($assignment->employee_first_name . ' ' . ($assignment->employee_last_name ?? ''));
                    }
                    if ($assignment->service_vehicle_id) {
                        $resources[] = 'Vehicle: ' . $assignment->registration_number;
                    }
                    if ($assignment->driver_id) {
                        $resources[] = 'Driver: ' . $assignment->driver_code . ' — '
                            . trim($assignment->driver_first_name . ' ' . ($assignment->driver_last_name ?? ''));
                    }
                    if ($assignment->assistant_id) {
                        $resources[] = 'Assistant: ' . $assignment->assistant_code . ' — '
                            . trim($assignment->assistant_first_name . ' ' . ($assignment->assistant_last_name ?? ''));
                    }
                    if (! $assignment->service_vehicle_id
                        && ! $assignment->employee_id
                        && ($assignment->driver_id || $assignment->assistant_id)) {
                        array_unshift($resources, 'Crew (vehicle moved)');
                    }

                    return $resources;
                })
                ->values()
                ->all();
        });

        return view('contracts.index', compact('contracts'));
    }

    public function checkNumber(Request $request)
    {
        $validated = $request->validate([
            'contract_number' => ['required', 'string', 'max:50'],
            'exclude_id' => ['nullable', 'integer', 'exists:contracts,id'],
        ]);

        $query = DB::table('contracts')
            ->where('contract_number', $validated['contract_number']);

        if (! empty($validated['exclude_id'])) {
            $query->where('id', '!=', $validated['exclude_id']);
        }

        return response()->json(['available' => ! $query->exists()]);
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
        $contract = DB::table('contracts')->where('id', $id)->first();
        abort_if(! $contract, 404, 'Contract not found.');

        $validated = $this->validatedContractData($request, $id);
        if (in_array($contract->status, ['completed', 'terminated'], true)
            && ! in_array($validated['status'], ['completed', 'terminated'], true)
            && $this->hasResourcesAssignedToAnotherActiveContract($id)) {
            throw ValidationException::withMessages([
                'status' => 'Resources from this contract have been assigned elsewhere. Move them back before reactivating this contract.',
            ]);
        }

        DB::transaction(function () use ($id, $contract, $validated) {
            DB::table('contracts')->where('id', $id)->update([
                ...$validated,
                'updated_at' => now(),
            ]);

            if ($contract->contract_type !== $validated['contract_type']) {
                DB::table('contract_service_assignments')->where('contract_id', $id)->delete();
            }
        });

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

    private function hasResourcesAssignedToAnotherActiveContract(int $contractId): bool
    {
        return DB::table('contract_service_assignments as source')
            ->where('source.contract_id', $contractId)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('contract_service_assignments as other')
                    ->join('contracts as active_contract', 'active_contract.id', '=', 'other.contract_id')
                    ->whereNotIn('active_contract.status', ['completed', 'terminated'])
                    ->where(function ($query) {
                        $query->whereColumn('other.employee_id', 'source.employee_id')
                            ->orWhereColumn('other.service_vehicle_id', 'source.service_vehicle_id')
                            ->orWhereColumn('other.driver_id', 'source.driver_id')
                            ->orWhereColumn('other.assistant_id', 'source.assistant_id');
                    });
            })
            ->exists();
    }

    private function validatedContractData(Request $request, ?int $contractId = null): array
    {
        $validated = $request->validate([
            'contract_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('contracts', 'contract_number')->ignore($contractId),
            ],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'organization' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:5000'],
            'contract_type' => ['required', Rule::in(['Manpower', 'Ambulance', 'Cabs'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'paid_holidays' => ['sometimes', 'boolean'],
            'leave_with_wages' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'active', 'completed', 'terminated'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['paid_holidays'] = $request->boolean('paid_holidays');
        $validated['leave_with_wages'] = $request->boolean('leave_with_wages');

        return $validated;
    }
}
