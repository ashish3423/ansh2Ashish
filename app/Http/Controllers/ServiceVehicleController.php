<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceVehicleController extends Controller
{
    public function index()
    {
        $vehicles = DB::table('service_vehicles')->orderBy('registration_number')->get();

        return view('service-vehicles.index', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedVehicleData($request);
        DB::table('service_vehicles')->insert([
            ...$validated,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('service-vehicles.index')->with('status', 'Vehicle added successfully.');
    }

    public function update(Request $request, int $id)
    {
        $vehicle = DB::table('service_vehicles')->where('id', $id)->first();
        abort_if(! $vehicle, 404, 'Vehicle not found.');
        $validated = $this->validatedVehicleData($request, $id);
        $hasAssignments = DB::table('contract_service_assignments')
            ->where('service_vehicle_id', $id)
            ->exists();
        if ($hasAssignments && $vehicle->vehicle_type !== $validated['vehicle_type']) {
            throw ValidationException::withMessages([
                'vehicle_type' => 'Unassign this vehicle from its contracts before changing its type.',
            ]);
        }
        if ($hasAssignments && $validated['status'] === 'inactive') {
            throw ValidationException::withMessages([
                'status' => 'Unassign this vehicle from its contracts before marking it inactive.',
            ]);
        }

        DB::table('service_vehicles')->where('id', $id)->update([
            ...$validated,
            'updated_at' => now(),
        ]);

        return redirect()->route('service-vehicles.index')->with('status', 'Vehicle updated successfully.');
    }

    public function destroy(int $id)
    {
        $deleted = DB::transaction(function () use ($id) {
            DB::table('contract_service_assignments')->where('service_vehicle_id', $id)->delete();

            return DB::table('service_vehicles')->where('id', $id)->delete();
        });
        abort_if(! $deleted, 404, 'Vehicle not found.');

        return redirect()->route('service-vehicles.index')->with('status', 'Vehicle deleted successfully.');
    }

    private function validatedVehicleData(Request $request, ?int $vehicleId = null): array
    {
        return $request->validate([
            'vehicle_type' => ['required', Rule::in(['Ambulance', 'Cabs'])],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('service_vehicles', 'registration_number')->ignore($vehicleId),
            ],
            'make_model' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
