<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('employees');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
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

        if ($request->filled('department')) {
            $query->where(
                'department',
                'like',
                '%' . $request->department . '%'
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('joining_date')) {
            $query->where('joining_date', $request->joining_date);
        }

        $employees = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('employees.index', compact('employees'));
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
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'leaving_date' => 'nullable|date|after_or_equal:joining_date',
            'status' => 'required|in:active,inactive',
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
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'leaving_date' => 'nullable|date|after_or_equal:joining_date',
            'status' => 'required|in:active,inactive',
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
