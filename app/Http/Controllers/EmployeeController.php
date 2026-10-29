<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {

        // Fetch unique department names
        $departments = DB::table('employees')
        ->whereNotNull('department')
        ->where('department', '!=', '')
        ->distinct()
        ->orderBy('department')
        ->pluck('department');

        $query = DB::table('employees');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('work_location', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('designation')) {
            $query->where(
                'designation',
                'like',
                '%' . $request->designation . '%'
            );
        }

        // if ($request->filled('department')) {
        //     $query->where(
        //         'department',
        //         'like',
        //         '%' . $request->department . '%'
        //     );
        // }

         // Department filter
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('joining_date')) {
            $query->where('joining_date', $request->joining_date);
        }







        // Police verification filter
        if ($request->input('police_verification_status') !== null
            && $request->input('police_verification_status') !== '') {
            $query->where(
                'police_verification_status',
                $request->input('police_verification_status')
            );
        }

        // ID card status filter
        if ($request->input('id_card_status') !== null
            && $request->input('id_card_status') !== '') {
            $query->where(
                'id_card_status',
                $request->input('id_card_status')
            );
        }

        // Dress status filter
        if ($request->input('dress_status') !== null
            && $request->input('dress_status') !== '') {
            $query->where(
                'dress_status',
                $request->input('dress_status')
            );
        }

















        $employees = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        //return view('employees.index', compact('employees'));

        return view('employees.index', compact('employees', 'departments'));

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => [
                'nullable', 'email', 'max:150',
                Rule::unique('employees', 'email'),
            ],
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'work_location' => 'nullable|string|max:100',
            'emp_type' => 'nullable|integer|in:0,1,2,3',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',

            'aadhar_number' => 'nullable|string|max:100',
            'driving_license_number' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:100',
            'uan_number' => 'nullable|string|max:100',
            'esi_number' => 'nullable|string|max:100',
            'reference_number1' => 'nullable|string|max:100',
            'reference_number2' => 'nullable|string|max:100',


            'id_card_status' => 'nullable|boolean',
            'dress_status' => 'nullable|boolean',
            'police_verification_status' => 'nullable|boolean',
            'police_certificate_number' => [
                'nullable',
                'string',
                'max:100',
                'required_if:police_verification_status,1',
            ],


            'joining_date' => 'nullable|date',
            'leaving_date' => 'nullable|date|after_or_equal:joining_date',
            'status' => 'required|in:active,inactive',
            'wages_rate_per_day' => 'nullable|numeric|min:0',
        ]);

        $request->merge([
            'id_card_status' => $request->boolean('id_card_status'),
            'dress_status' => $request->boolean('dress_status'),
            'police_verification_status' =>
                $request->boolean('police_verification_status'),
        ]);

        $photo = $request->file('photo')?->store('employees', 'public');
        if ($request->hasFile('photo') && !$photo) {
            throw new \RuntimeException('Unable to store the employee photo.');
        }

        $validated['employee_code'] = $this->generateEmployeeCode();

        DB::table('employees')->insert([
            ...$validated,
            'photo' => $photo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Employee added successfully.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = DB::table('employees')->where('id', $id)->first();

        abort_if(!$employee, 404, 'Employee not found.');


       

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => [
                'nullable', 'email', 'max:150',
                Rule::unique('employees', 'email')->ignore($id),
            ],
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'emp_type' => 'nullable|integer|in:0,1,2,3',
            'work_location' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',

            'aadhar_number' => 'nullable|string|max:100',
            'driving_license_number' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:100',
            'uan_number' => 'nullable|string|max:100',
            'esi_number' => 'nullable|string|max:100',
            'reference_number1' => 'nullable|string|max:100',
            'reference_number2' => 'nullable|string|max:100',

            'id_card_status' => 'nullable|boolean',
            'dress_status' => 'nullable|boolean',
            'police_verification_status' => 'nullable|boolean',
            'police_certificate_number' => [
                'nullable',
                'string',
                'max:100',
                'required_if:police_verification_status,1',
            ],


            'joining_date' => 'nullable|date',
            'leaving_date' => 'nullable|date|after_or_equal:joining_date',
            'status' => 'required|in:active,inactive',
            'wages_rate_per_day' => 'nullable|numeric|min:0',
        ]);

        if ($validated['status'] === 'inactive'
            && DB::table('contract_service_assignments')
                ->where('employee_id', $id)
                ->orWhere('driver_id', $id)
                ->orWhere('assistant_id', $id)
                ->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Remove this employee from contract service assignments before marking them inactive.',
            ]);
        }

        unset($validated['photo']);

         $request->merge([
             'id_card_status' => $request->boolean('id_card_status'),
             'dress_status' => $request->boolean('dress_status'),
             'police_verification_status' =>
                 $request->boolean('police_verification_status'),
         ]);

        $photo = $request->file('photo')?->store('employees', 'public');
        if ($request->hasFile('photo') && !$photo) {
            throw new \RuntimeException('Unable to store the employee photo.');
        }

        $updates = [
            ...$validated,
            'updated_at' => now(),
        ];

        if ($photo) {
            $updates['photo'] = $photo;
        }

        DB::table('employees')
            ->where('id', $id)
            ->update($updates);

        if ($photo && $employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        return response()->json([
            'message' => 'Employee updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $employee = DB::table('employees')->where('id', $id)->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        }

        $deleted = DB::transaction(function () use ($id) {
            DB::table('contract_service_assignments')
                ->where('employee_id', $id)
                ->orWhere('driver_id', $id)
                ->orWhere('assistant_id', $id)
                ->delete();

            return DB::table('employees')->where('id', $id)->delete();
        });

        if (!$deleted) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        }

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }

    private function generateEmployeeCode(): string
    {
        $period = now()->format('my');
        $prefix = "AE/{$period}/";
        $highestSerial = DB::table('employees')
            ->where('employee_code', 'like', $prefix . '%')
            ->pluck('employee_code')
            ->map(function (string $code) use ($prefix): int {
                $serial = substr($code, strlen($prefix));

                return ctype_digit($serial) ? (int) $serial : 0;
            })
            ->max() ?? 0;

        return $prefix . ($highestSerial + 1);
    }
}
