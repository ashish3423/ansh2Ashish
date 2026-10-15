<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('employees', 'employee_code'),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => [
                'nullable', 'email', 'max:150',
                Rule::unique('employees', 'email'),
            ],
            'phone' => 'nullable|string|max:20',
            'work_location' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',

            'aadhar_number' => 'nullable|string|max:100',
            'driving_license_number' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:100',
            'uan_number' => 'nullable|string|max:100',
            'esi_number' => 'nullable|string|max:100',


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
        ]);

          $request->merge([
    'id_card_status' => $request->boolean('id_card_status'),
    'dress_status' => $request->boolean('dress_status'),
    'police_verification_status' =>
        $request->boolean('police_verification_status'),
]);

        DB::table('employees')->insert([
            ...$validated,
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
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('employees', 'employee_code')->ignore($id),
            ],
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => [
                'nullable', 'email', 'max:150',
                Rule::unique('employees', 'email')->ignore($id),
            ],
            'phone' => 'nullable|string|max:20',
            'work_location' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',

            'aadhar_number' => 'nullable|string|max:100',
            'driving_license_number' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:100',
            'uan_number' => 'nullable|string|max:100',
            'esi_number' => 'nullable|string|max:100',


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
        ]);


         $request->merge([
    'id_card_status' => $request->boolean('id_card_status'),
    'dress_status' => $request->boolean('dress_status'),
    'police_verification_status' =>
        $request->boolean('police_verification_status'),
]);

        DB::table('employees')
            ->where('id', $id)
            ->update([
                ...$validated,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Employee updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $deleted = DB::table('employees')
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }
}
