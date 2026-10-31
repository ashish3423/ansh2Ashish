<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContractServiceController extends Controller
{
    public function edit(int $contractId, Request $request)
    {
        $contract = DB::table('contracts')->find($contractId);
        abort_if(! $contract, 404, 'Contract not found.');

        $employees = DB::table('employees')
            ->select('id', 'employee_code', 'first_name', 'last_name')
            ->where('status', 'active')
            ->whereNotExists(function ($query) use ($contractId) {
                $query->selectRaw('1')
                    ->from('contract_service_assignments as assigned')
                    ->join('contracts as assigned_contract', 'assigned_contract.id', '=', 'assigned.contract_id')
                    ->where('assigned.contract_id', '!=', $contractId)
                    ->whereNotIn('assigned_contract.status', ['completed', 'terminated'])
                    ->where(function ($query) {
                        $query->whereColumn('assigned.employee_id', 'employees.id')
                            ->orWhereColumn('assigned.driver_id', 'employees.id')
                            ->orWhereColumn('assigned.assistant_id', 'employees.id');
                    });
            })
            ->orderBy('first_name')
            ->get();
        $vehicles = DB::table('service_vehicles')
            ->where('vehicle_type', $contract->contract_type)
            ->where('status', 'active')
            ->whereNotExists(function ($query) use ($contractId) {
                $query->selectRaw('1')
                    ->from('contract_service_assignments as assigned')
                    ->join('contracts as assigned_contract', 'assigned_contract.id', '=', 'assigned.contract_id')
                    ->where('assigned.contract_id', '!=', $contractId)
                    ->whereNotIn('assigned_contract.status', ['completed', 'terminated'])
                    ->whereColumn('assigned.service_vehicle_id', 'service_vehicles.id');
            })
            ->orderBy('registration_number')
            ->get();
        $assignments = DB::table('contract_service_assignments as a')
            ->leftJoin('employees as e', 'e.id', '=', 'a.employee_id')
            ->leftJoin('service_vehicles as v', 'v.id', '=', 'a.service_vehicle_id')
            ->leftJoin('employees as d', 'd.id', '=', 'a.driver_id')
            ->leftJoin('employees as assistant', 'assistant.id', '=', 'a.assistant_id')
            ->where('a.contract_id', $contractId)
            ->select(
                'a.*',
                'e.employee_code',
                'e.first_name as employee_first_name',
                'e.last_name as employee_last_name',
                'v.registration_number',
                'v.make_model',
                'd.employee_code as driver_code',
                'd.first_name as driver_first_name',
                'd.last_name as driver_last_name',
                'assistant.employee_code as assistant_code',
                'assistant.first_name as assistant_first_name',
                'assistant.last_name as assistant_last_name'
            )
            ->get();
        $moveTargets = DB::table('contracts')
            ->where('id', '!=', $contractId)
            ->whereNotIn('status', ['completed', 'terminated'])
            ->orderByDesc('id')
            ->get(['id', 'contract_number']);
        $modalMode = $request->boolean('modal');

        return view('contract-services.edit', compact(
            'contract',
            'employees',
            'vehicles',
            'assignments',
            'moveTargets',
            'modalMode'
        ));
    }

    public function editMove(int $contractId, int $assignmentId, Request $request)
    {
        $sourceContract = DB::table('contracts')->find($contractId);
        abort_if(! $sourceContract, 404, 'Contract not found.');
        $assignment = DB::table('contract_service_assignments')
            ->where('id', $assignmentId)
            ->where('contract_id', $contractId)
            ->first();
        abort_if(! $assignment, 404, 'Service assignment not found.');

        $resource = $request->query('resource');
        abort_unless(in_array($resource, ['vehicle', 'employee', 'driver', 'assistant'], true), 404);
        if ($resource === 'vehicle') {
            abort_if(! $assignment->service_vehicle_id, 404, 'Vehicle is not assigned.');
            $item = DB::table('service_vehicles')->find($assignment->service_vehicle_id);
            $label = $item->registration_number;
        } else {
            $employeeId = match ($resource) {
                'employee' => $assignment->employee_id,
                'driver' => $assignment->driver_id,
                'assistant' => $assignment->assistant_id,
            };
            abort_if(! $employeeId, 404, 'Employee is not assigned in this role.');
            $item = DB::table('employees')->find($employeeId);
            $label = trim($item->employee_code.' — '.$item->first_name.' '.($item->last_name ?? ''));
        }

        $targets = DB::table('contracts')
            ->where('id', '!=', $contractId)
            ->whereNotIn('status', ['completed', 'terminated'])
            ->orderByDesc('id')
            ->get(['id', 'contract_number', 'contract_type']);
        $targetVehicles = DB::table('contract_service_assignments as a')
            ->join('contracts as c', 'c.id', '=', 'a.contract_id')
            ->join('service_vehicles as v', 'v.id', '=', 'a.service_vehicle_id')
            ->where('a.contract_id', '!=', $contractId)
            ->whereNotIn('c.status', ['completed', 'terminated'])
            ->where('v.status', 'active')
            ->select(
                'a.id as assignment_id',
                'a.contract_id',
                'c.contract_type',
                'v.registration_number',
                'a.driver_id',
                'a.assistant_id'
            )
            ->get();
        $modalMode = $request->boolean('modal');

        return view('contract-services.move', compact(
            'sourceContract',
            'assignment',
            'contractId',
            'assignmentId',
            'resource',
            'label',
            'targets',
            'targetVehicles',
            'modalMode'
        ));
    }

    public function move(Request $request, int $contractId, int $assignmentId)
    {
        $sourceContract = DB::table('contracts')->find($contractId);
        abort_if(! $sourceContract, 404, 'Contract not found.');
        $validated = $request->validate([
            'resource' => ['required', Rule::in(['vehicle', 'employee', 'driver', 'assistant'])],
            'target_contract_id' => ['required', 'integer', 'exists:contracts,id'],
            'target_role' => ['nullable', Rule::in(['employee', 'driver', 'assistant'])],
            'target_assignment_id' => ['nullable', 'integer', 'exists:contract_service_assignments,id'],
        ]);
        $targetId = (int) $validated['target_contract_id'];
        if ($targetId === $contractId) {
            throw ValidationException::withMessages(['target_contract_id' => 'Choose a different contract.']);
        }
        $target = DB::table('contracts')->find($targetId);
        abort_if(! $target, 404, 'Destination contract not found.');
        if (in_array($target->status, ['completed', 'terminated'], true)) {
            throw ValidationException::withMessages([
                'target_contract_id' => 'Resources can only be moved to a contract that is not completed or terminated.',
            ]);
        }

        DB::transaction(function () use ($contractId, $assignmentId, $targetId, $target, $sourceContract, $validated) {
            $source = DB::table('contract_service_assignments')
                ->where('id', $assignmentId)
                ->where('contract_id', $contractId)
                ->lockForUpdate()
                ->first();
            abort_if(! $source, 404, 'Service assignment not found.');
            $resource = $validated['resource'];

            if ($resource === 'vehicle') {
                if ($target->contract_type !== $sourceContract->contract_type) {
                    throw ValidationException::withMessages([
                        'target_contract_id' => 'Vehicles can only move to a contract with the same service type.',
                    ]);
                }
                $vehicleId = $source->service_vehicle_id;
                abort_if(! $vehicleId, 404, 'Vehicle is not assigned.');
                if (! $this->vehicleAvailable($vehicleId, $contractId)) {
                    throw ValidationException::withMessages([
                        'target_contract_id' => 'This vehicle is already assigned to another active contract.',
                    ]);
                }
                $vehicle = DB::table('service_vehicles')->find($vehicleId);
                if (! $vehicle
                    || $vehicle->vehicle_type !== $target->contract_type
                    || $vehicle->status !== 'active') {
                    throw ValidationException::withMessages([
                        'target_contract_id' => 'Only active vehicles matching the destination contract can be moved.',
                    ]);
                }
                $duplicate = DB::table('contract_service_assignments')
                    ->where('contract_id', $targetId)
                    ->where('service_vehicle_id', $vehicleId)
                    ->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'target_contract_id' => 'This vehicle is already assigned to the destination contract.',
                    ]);
                }
                DB::table('contract_service_assignments')->where('id', $assignmentId)->update([
                    'service_vehicle_id' => null,
                    'updated_at' => now(),
                ]);
                DB::table('contract_service_assignments')->insert([
                    'contract_id' => $targetId,
                    'service_vehicle_id' => $vehicleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->deleteEmptyAssignment($assignmentId);

                return;
            }

            $employeeId = match ($resource) {
                'employee' => $source->employee_id,
                'driver' => $source->driver_id,
                'assistant' => $source->assistant_id,
            };
            abort_if(! $employeeId, 404, 'Employee is not assigned in this role.');
            if (! $this->employeeAvailable($employeeId, $contractId)) {
                throw ValidationException::withMessages([
                    'target_contract_id' => 'This employee is already assigned to another active contract.',
                ]);
            }
            $role = $validated['target_role'] ?? null;
            if ($target->contract_type === 'Manpower') {
                if ($role !== 'employee') {
                    throw ValidationException::withMessages([
                        'target_role' => 'Choose Employee for a manpower contract.',
                    ]);
                }
                $conflict = DB::table('contract_service_assignments')
                    ->where('contract_id', $targetId)
                    ->where(function ($query) use ($employeeId) {
                        $query->where('employee_id', $employeeId)
                            ->orWhere('driver_id', $employeeId)
                            ->orWhere('assistant_id', $employeeId);
                    })->exists();
                if ($conflict) {
                    throw ValidationException::withMessages([
                        'target_contract_id' => 'This employee is already assigned to the destination contract.',
                    ]);
                }
                $this->clearEmployeeFromSource($assignmentId, $resource);
                DB::table('contract_service_assignments')->insert([
                    'contract_id' => $targetId,
                    'employee_id' => $employeeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->deleteEmptyAssignment($assignmentId);

                return;
            }

            if (! in_array($role, ['driver', 'assistant'], true)
                || ($role === 'assistant' && $target->contract_type !== 'Ambulance')) {
                throw ValidationException::withMessages([
                    'target_role' => 'Choose a valid crew role for the destination contract.',
                ]);
            }
            $targetAssignmentId = (int) ($validated['target_assignment_id'] ?? 0);
            $targetAssignment = DB::table('contract_service_assignments')
                ->join('service_vehicles as v', 'v.id', '=', 'contract_service_assignments.service_vehicle_id')
                ->where('contract_service_assignments.id', $targetAssignmentId)
                ->where('contract_service_assignments.contract_id', $targetId)
                ->where('v.vehicle_type', $target->contract_type)
                ->where('v.status', 'active')
                ->select('contract_service_assignments.*')
                ->lockForUpdate()
                ->first();
            if (! $targetAssignment) {
                throw ValidationException::withMessages([
                    'target_assignment_id' => 'Select a vehicle assigned to the destination contract.',
                ]);
            }
            if (($role === 'driver' && $targetAssignment->driver_id)
                || ($role === 'assistant' && $targetAssignment->assistant_id)) {
                throw ValidationException::withMessages([
                    'target_assignment_id' => 'The selected vehicle already has someone in that role.',
                ]);
            }
            $employeeConflict = DB::table('contract_service_assignments')
                ->where('contract_id', $targetId)
                ->where(function ($query) use ($employeeId) {
                    $query->where('employee_id', $employeeId)
                        ->orWhere('driver_id', $employeeId)
                        ->orWhere('assistant_id', $employeeId);
                })->exists();
            if ($employeeConflict) {
                throw ValidationException::withMessages([
                    'target_contract_id' => 'This employee is already assigned to the destination contract.',
                ]);
            }
            $this->clearEmployeeFromSource($assignmentId, $resource);
            DB::table('contract_service_assignments')->where('id', $targetAssignmentId)->update([
                $role === 'driver' ? 'driver_id' : 'assistant_id' => $employeeId,
                'updated_at' => now(),
            ]);
            $this->deleteEmptyAssignment($assignmentId);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Service resource moved successfully.',
                'url' => route('contracts.services.edit', [
                    $targetId,
                    ...($request->boolean('modal') ? ['modal' => 1] : []),
                ]),
            ]);
        }

        return redirect()->route('contracts.services.edit', $targetId)
            ->with('status', 'Service resource moved successfully.');
    }

    private function clearEmployeeFromSource(int $assignmentId, string $resource): void
    {
        $column = match ($resource) {
            'employee' => 'employee_id',
            'driver' => 'driver_id',
            'assistant' => 'assistant_id',
        };
        DB::table('contract_service_assignments')->where('id', $assignmentId)->update([
            $column => null,
            'updated_at' => now(),
        ]);
    }

    private function deleteEmptyAssignment(int $assignmentId): void
    {
        DB::table('contract_service_assignments')
            ->where('id', $assignmentId)
            ->whereNull('employee_id')
            ->whereNull('service_vehicle_id')
            ->whereNull('driver_id')
            ->whereNull('assistant_id')
            ->delete();
    }

    public function remove(int $contractId, int $assignmentId, Request $request)
    {
        $contract = DB::table('contracts')->find($contractId);
        abort_if(! $contract, 404, 'Contract not found.');

        DB::transaction(function () use ($contractId, $assignmentId) {
            $assignment = DB::table('contract_service_assignments')
                ->where('id', $assignmentId)
                ->where('contract_id', $contractId)
                ->lockForUpdate()
                ->first();
            abort_if(! $assignment, 404, 'Service assignment not found.');

            DB::table('contract_service_assignments')
                ->where('id', $assignmentId)
                ->delete();
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Service assignment removed successfully.']);
        }

        return redirect()->route('contracts.services.edit', $contractId)
            ->with('status', 'Service assignment removed successfully.');
    }

    public function update(Request $request, int $contractId)
    {
        $contract = DB::table('contracts')->find($contractId);
        abort_if(! $contract, 404, 'Contract not found.');
        if (in_array($contract->status, ['completed', 'terminated'], true)) {
            throw ValidationException::withMessages([
                'assignments' => 'Resources cannot be assigned to a completed or terminated contract.',
            ]);
        }

        $assignments = $request->input('assignments', []);
        if (! is_array($assignments) || ! array_is_list($assignments)) {
            throw ValidationException::withMessages([
                'assignments' => 'Assignments must be a list.',
            ]);
        }

        $rules = ['assignments' => ['array', 'max:500']];
        foreach (array_keys($assignments) as $index) {
            if ($contract->contract_type === 'Manpower') {
                $rules["assignments.{$index}.employee_id"] = [
                    'required',
                    'integer',
                    'distinct',
                    Rule::exists('employees', 'id')->where('status', 'active'),
                ];
            } else {
                $rules["assignments.{$index}.service_vehicle_id"] = [
                    ...($contract->contract_type === 'Ambulance' ? ['required'] : ['nullable']),
                    'integer',
                    'distinct',
                    'exists:service_vehicles,id',
                ];
                $rules["assignments.{$index}.driver_id"] = [
                    'nullable',
                    'integer',
                    Rule::exists('employees', 'id')->where('status', 'active'),
                ];
                if ($contract->contract_type === 'Ambulance') {
                    $rules["assignments.{$index}.assistant_id"] = [
                        'nullable',
                        'integer',
                        'different:assignments.'.$index.'.driver_id',
                        Rule::exists('employees', 'id')->where('status', 'active'),
                    ];
                }
            }
        }

        $validated = $request->validate($rules);
        $availabilityErrors = [];
        $seenVehicles = [];
        $seenEmployees = $contract->contract_type === 'Manpower'
            ? []
            : DB::table('contract_service_assignments')
                ->where('contract_id', $contractId)
                ->whereNotNull('employee_id')
                ->pluck('employee_id')
                ->mapWithKeys(fn ($employeeId) => [$employeeId => true])
                ->all();
        foreach ($validated['assignments'] ?? [] as $index => $assignment) {
            if ($contract->contract_type === 'Manpower') {
                $field = "assignments.{$index}.employee_id";
                if (! $this->employeeAvailable($assignment['employee_id'], $contractId)) {
                    $availabilityErrors[$field] = 'This employee is already assigned to another contract.';
                }

                continue;
            }

            if (! empty($assignment['employee_id'])) {
                $seenEmployees[$assignment['employee_id']] = true;
            }

            $vehicleField = "assignments.{$index}.service_vehicle_id";
            if (! empty($assignment['service_vehicle_id'])) {
                $vehicleId = $assignment['service_vehicle_id'];
                if (isset($seenVehicles[$vehicleId])) {
                    $availabilityErrors[$vehicleField] = 'A vehicle can only be assigned once to this contract.';
                }
                $seenVehicles[$vehicleId] = true;
            }
            if (! empty($assignment['service_vehicle_id'])
                && ! $this->vehicleAvailable($assignment['service_vehicle_id'], $contractId)) {
                $availabilityErrors[$vehicleField] = 'This vehicle is already assigned to another contract.';
            }

            foreach (['driver_id', 'assistant_id'] as $employeeField) {
                if (! empty($assignment[$employeeField])) {
                    $employeeId = $assignment[$employeeField];
                    if (isset($seenEmployees[$employeeId])) {
                        $availabilityErrors["assignments.{$index}.{$employeeField}"] =
                            'An employee can only be assigned once to this contract.';
                    }
                    $seenEmployees[$employeeId] = true;
                    if (! $this->employeeAvailable($employeeId, $contractId)) {
                        $availabilityErrors["assignments.{$index}.{$employeeField}"] =
                            'This employee is already assigned to another contract.';
                    }
                }
            }
            if (empty($assignment['service_vehicle_id'])
                && empty($assignment['driver_id'])
                && empty($assignment['assistant_id'])) {
                $availabilityErrors["assignments.{$index}.service_vehicle_id"] =
                    'Each row must contain a vehicle or at least one assigned employee.';
            }
        }
        if ($availabilityErrors) {
            throw ValidationException::withMessages($availabilityErrors);
        }

        $rows = [];
        $vehicleIds = collect($validated['assignments'] ?? [])
            ->pluck('service_vehicle_id')
            ->filter()
            ->unique()
            ->all();
        $vehicles = DB::table('service_vehicles')
            ->whereIn('id', $vehicleIds)
            ->get()
            ->keyBy('id');

        foreach ($validated['assignments'] ?? [] as $assignment) {
            if ($contract->contract_type === 'Manpower') {
                $rows[] = [
                    'employee_id' => $assignment['employee_id'],
                    'service_vehicle_id' => null,
                    'driver_id' => null,
                    'assistant_id' => null,
                ];

                continue;
            }

            $vehicle = $vehicles->get($assignment['service_vehicle_id'] ?? null);
            if (! empty($assignment['service_vehicle_id'])
                && (! $vehicle || $vehicle->vehicle_type !== $contract->contract_type || $vehicle->status !== 'active')) {
                throw ValidationException::withMessages([
                    'assignments' => 'Select an active vehicle matching the contract service type.',
                ]);
            }

            $rows[] = [
                'employee_id' => null,
                'service_vehicle_id' => $vehicle?->id,
                'driver_id' => $assignment['driver_id'] ?? null,
                'assistant_id' => $contract->contract_type === 'Ambulance'
                    ? ($assignment['assistant_id'] ?? null)
                    : null,
            ];
        }

        DB::transaction(function () use ($contractId, $rows) {
            DB::table('contract_service_assignments')->where('contract_id', $contractId)->delete();
            $now = now();
            foreach ($rows as &$row) {
                $row['contract_id'] = $contractId;
                $row['created_at'] = $now;
                $row['updated_at'] = $now;
            }
            unset($row);

            if ($rows) {
                DB::table('contract_service_assignments')->insert($rows);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Service assignments saved successfully.',
                'url' => route('contracts.services.edit', [
                    $contractId,
                    ...($request->boolean('modal') ? ['modal' => 1] : []),
                ]),
            ]);
        }

        return redirect()->route('contracts.services.edit', [
            $contractId,
            ...($request->boolean('modal') ? ['modal' => 1] : []),
        ])->with('status', 'Service assignments saved successfully.');
    }

    private function employeeAvailable(int|string $employeeId, int $contractId): bool
    {
        return ! DB::table('contract_service_assignments as assigned')
            ->join('contracts as assigned_contract', 'assigned_contract.id', '=', 'assigned.contract_id')
            ->where('assigned.contract_id', '!=', $contractId)
            ->whereNotIn('assigned_contract.status', ['completed', 'terminated'])
            ->where(function ($query) use ($employeeId) {
                $query->where('assigned.employee_id', $employeeId)
                    ->orWhere('assigned.driver_id', $employeeId)
                    ->orWhere('assigned.assistant_id', $employeeId);
            })
            ->exists();
    }

    private function vehicleAvailable(int|string $vehicleId, int $contractId): bool
    {
        return ! DB::table('contract_service_assignments as assigned')
            ->join('contracts as assigned_contract', 'assigned_contract.id', '=', 'assigned.contract_id')
            ->where('assigned.service_vehicle_id', $vehicleId)
            ->where('assigned.contract_id', '!=', $contractId)
            ->whereNotIn('assigned_contract.status', ['completed', 'terminated'])
            ->exists();
    }
}
