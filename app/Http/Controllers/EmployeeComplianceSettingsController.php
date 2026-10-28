<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeComplianceSettingsController extends Controller
{
    public function index(): View
    {
        return view('employee-compliance-settings.index', [
            'settings' => $this->settings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'epf_employee_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'epf_employer_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'esi_employee_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'esi_employer_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'esi_eligibility_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'epf_eligibility_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'bonus_eligibility_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
        ]);

        DB::table('employee_compliance_settings')->updateOrInsert(
            ['id' => 1],
            [...$validated, 'updated_at' => now(), 'created_at' => $this->settings()->created_at ?? now()]
        );

        return redirect()->route('employee-compliance-settings.index')
            ->with('success', 'EPF, ESI, and bonus settings saved successfully.');
    }

    private function settings(): object
    {
        $settings = DB::table('employee_compliance_settings')->where('id', 1)->first();

        if ($settings) {
            return $settings;
        }

        DB::table('employee_compliance_settings')->insert([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('employee_compliance_settings')->where('id', 1)->first();
    }
}
