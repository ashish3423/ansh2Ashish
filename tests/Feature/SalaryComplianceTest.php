<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SalaryComplianceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array',
        ]);
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        $roleMigration = require base_path('database/migrations/2026_10_27_000000_add_role_to_users_table.php');
        $roleMigration->up();

        Schema::connection('sqlite')->create('employees', function ($table) {
            $table->id();
            $table->string('employee_code');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('work_location')->nullable();
            $table->decimal('wages_rate_per_day', 12, 2)->nullable();
            $table->unsignedTinyInteger('emp_type')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
        $employeeComplianceMigration = require base_path('database/migrations/2026_10_31_000001_add_epf_esi_eligibility_to_employees_table.php');
        $employeeComplianceMigration->up();
        Schema::connection('sqlite')->create('employee_salaries', function ($table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('hra', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('net_salary', 12, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });
        $contractMigration = require base_path('database/migrations/2026_10_27_000004_create_contracts_table.php');
        $contractMigration->up();
        $optionalContractEmployeeMigration = require base_path('database/migrations/2026_10_28_000001_update_contract_details_and_make_employee_optional.php');
        $optionalContractEmployeeMigration->up();
        $removeContractTitleMigration = require base_path('database/migrations/2026_10_29_000002_remove_title_from_contracts_table.php');
        $removeContractTitleMigration->up();
        $holidayOptionsMigration = require base_path('database/migrations/2026_10_29_000003_add_holiday_options_to_contracts_table.php');
        $holidayOptionsMigration->up();
        $contractExtensionMigration = require base_path('database/migrations/2026_10_31_000000_add_extension_date_and_holiday_percentages_to_contracts_table.php');
        $contractExtensionMigration->up();
        $contractCategoryBonusMigration = require base_path('database/migrations/2026_10_31_000002_add_category_and_bonus_to_contracts_table.php');
        $contractCategoryBonusMigration->up();
        Schema::connection('sqlite')->create('contract_service_assignments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('service_vehicle_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('assistant_id')->nullable();
            $table->timestamps();
        });
        $salaryMigration = require base_path('database/migrations/2026_10_27_000002_add_compliance_fields_to_employee_salaries_table.php');
        $salaryMigration->up();
        $totalSalaryMigration = require base_path('database/migrations/2026_10_27_000003_add_total_salary_to_employee_salaries_table.php');
        $totalSalaryMigration->up();
        $contractBonusSalaryMigration = require base_path('database/migrations/2026_10_31_000003_add_contract_bonus_amount_to_employee_salaries_table.php');
        $contractBonusSalaryMigration->up();

        Schema::connection('sqlite')->create('employee_compliance_settings', function ($table) {
            $table->id();
            $table->decimal('epf_employee_percentage', 5, 2)->nullable();
            $table->decimal('epf_employer_percentage', 5, 2)->nullable();
            $table->decimal('esi_employee_percentage', 5, 2)->nullable();
            $table->decimal('esi_employer_percentage', 5, 2)->nullable();
            $table->decimal('esi_eligibility_amount', 12, 2)->nullable();
            $table->decimal('epf_eligibility_amount', 12, 2)->nullable();
            $table->decimal('bonus_eligibility_amount', 12, 2)->nullable();
            $table->timestamps();
        });
        DB::table('employee_compliance_settings')->insert([
            'id' => 1,
            'epf_employee_percentage' => 10,
            'epf_employer_percentage' => 12,
            'esi_employee_percentage' => 1,
            'esi_employer_percentage' => 2,
            'esi_eligibility_amount' => 21000,
            'epf_eligibility_amount' => 2000,
            'bonus_eligibility_amount' => 1999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_salary_includes_contract_holiday_percentages_in_gross_and_net(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        DB::table('employee_compliance_settings')
            ->where('id', 1)
            ->update(['bonus_eligibility_amount' => 2001]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-001',
            'first_name' => 'Eligible',
            'wages_rate_per_day' => 100,
        ]);
        DB::table('contracts')->insert([
            'employee_id' => $employeeId,
            'contract_number' => 'CON-001',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'paid_holidays' => 1,
            'paid_holidays_percentage' => 5,
            'leave_with_wages' => 1,
            'leave_with_wages_percentage' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 20,
                'bonus_amount' => 50,
            ])
            ->assertOk();

        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $employeeId,
            'effective_from' => now()->format('Y-m-01'),
            'total_salary' => 2000,
            'eligibility_salary' => 2000,
            'bonus_amount' => 50,
            'epf_employee_contribution' => 200,
            'epf_employer_contribution' => 240,
            'esi_employee_contribution' => 20,
            'esi_employer_contribution' => 40,
            'gross_salary' => 2630,
            'deductions' => 220,
            'net_salary' => 2130,
            'basic_salary' => 0,
            'hra' => 0,
            'allowances' => 0,
        ]);

        $salaryId = DB::table('employee_salaries')->where('employee_id', $employeeId)->value('id');
        $this->actingAs($user)
            ->putJson(route('employee-salaries.update', $salaryId), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 10,
                'bonus_amount' => 0,
            ])
            ->assertOk();

        $this->assertDatabaseHas('employee_salaries', [
            'id' => $salaryId,
            'total_salary' => 1000,
            'gross_salary' => 1170,
            'net_salary' => 1140,
        ]);
    }

    public function test_both_epf_shares_are_calculated_from_the_eligibility_amount_and_rounded(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        DB::table('employee_compliance_settings')
            ->where('id', 1)
            ->update(['epf_eligibility_amount' => 2001.25]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-005',
            'first_name' => 'Above Threshold',
            'wages_rate_per_day' => 100,
        ]);

        $this->actingAs($user)
            ->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 30,
            ])
            ->assertOk();

        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $employeeId,
            'total_salary' => 3000,
            'epf_employee_contribution' => 200,
            'epf_employer_contribution' => 240,
        ]);
    }

    public function test_esi_shares_use_actual_salary_and_round_when_within_the_eligibility_limit(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        DB::table('employee_compliance_settings')
            ->where('id', 1)
            ->update([
                'esi_employee_percentage' => 1.25,
                'esi_employer_percentage' => 2.5,
                'esi_eligibility_amount' => 21000,
            ]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-006',
            'first_name' => 'ESI Eligible',
            'wages_rate_per_day' => 100,
        ]);

        $this->actingAs($user)
            ->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 19,
            ])
            ->assertOk();

        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $employeeId,
            'total_salary' => 1900,
            'esi_employee_contribution' => 24,
            'esi_employer_contribution' => 48,
        ]);
    }

    public function test_esi_contributions_are_zero_when_salary_exceeds_eligibility_amount(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        DB::table('employee_compliance_settings')
            ->where('id', 1)
            ->update(['esi_eligibility_amount' => 1999]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-007',
            'first_name' => 'ESI Ineligible',
            'wages_rate_per_day' => 100,
        ]);

        $this->actingAs($user)
            ->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 20,
            ])
            ->assertOk();

        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $employeeId,
            'total_salary' => 2000,
            'esi_employee_contribution' => 0,
            'esi_employer_contribution' => 0,
        ]);
    }

    public function test_employee_epf_and_esi_switches_independently_control_salary_contributions(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $epfDisabledEmployeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-EPF-OFF',
            'first_name' => 'EPF Disabled',
            'wages_rate_per_day' => 100,
            'epf_enabled' => false,
            'esi_enabled' => true,
        ]);
        $esiDisabledEmployeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-ESI-OFF',
            'first_name' => 'ESI Disabled',
            'wages_rate_per_day' => 100,
            'epf_enabled' => true,
            'esi_enabled' => false,
        ]);

        $this->actingAs($user);
        foreach ([$epfDisabledEmployeeId, $esiDisabledEmployeeId] as $employeeId) {
            $this->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 20,
            ])->assertOk();
        }

        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $epfDisabledEmployeeId,
            'epf_employee_contribution' => 0,
            'epf_employer_contribution' => 0,
            'esi_employee_contribution' => 20,
            'esi_employer_contribution' => 40,
            'gross_salary' => 2040,
            'deductions' => 20,
            'net_salary' => 1980,
        ]);
        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $esiDisabledEmployeeId,
            'epf_employee_contribution' => 200,
            'epf_employer_contribution' => 240,
            'esi_employee_contribution' => 0,
            'esi_employer_contribution' => 0,
            'gross_salary' => 2240,
            'deductions' => 200,
            'net_salary' => 1800,
        ]);

        $this->get(route('employee-salaries.index'))
            ->assertOk()
            ->assertSee('data-epf-enabled="0"', false)
            ->assertSee('data-esi-enabled="1"', false)
            ->assertSee('data-epf-enabled="1"', false)
            ->assertSee('data-esi-enabled="0"', false)
            ->assertSee('data-compliance-field="epf"', false)
            ->assertSee('data-compliance-field="esi"', false);
    }

    public function test_enabled_contract_bonus_percentage_is_added_to_salary_calculation(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-CONTRACT-BONUS',
            'first_name' => 'Contract Bonus',
            'wages_rate_per_day' => 100,
        ]);
        DB::table('contracts')->insert([
            'employee_id' => $employeeId,
            'contract_number' => 'CON-BONUS-10',
            'contract_type' => 'Manpower',
            'contract_category' => 'regular_contract',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'bonus_enabled' => true,
            'bonus_percentage' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 20,
            ])
            ->assertOk();

        $this->assertDatabaseHas('employee_salaries', [
            'employee_id' => $employeeId,
            'total_salary' => 2000,
            'bonus_amount' => 0,
            'contract_bonus_amount' => 200,
            'epf_employee_contribution' => 200,
            'epf_employer_contribution' => 240,
            'esi_employee_contribution' => 20,
            'esi_employer_contribution' => 40,
            'gross_salary' => 2480,
            'deductions' => 220,
            'net_salary' => 1980,
        ]);

        $this->get(route('employee-salaries.index'))
            ->assertOk()
            ->assertSee('data-contract-bonus-enabled="1"', false)
            ->assertSee('data-contract-bonus-percentage="10"', false);
    }

    public function test_employee_edit_can_disable_epf_and_esi_switches(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-EDIT-COMPLIANCE',
            'first_name' => 'Edit Compliance',
            'status' => 'active',
            'epf_enabled' => true,
            'esi_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->putJson(route('employees.update', $employeeId), [
                'first_name' => 'Edit Compliance',
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Employee updated successfully.']);

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'epf_enabled' => false,
            'esi_enabled' => false,
        ]);
    }

    public function test_bonus_requires_salary_to_be_strictly_less_than_threshold(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-002',
            'first_name' => 'At Threshold',
            'wages_rate_per_day' => 100,
        ]);

        $this->actingAs($user)
            ->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 19.98,
                'bonus_amount' => 1,
            ])
            ->assertOk();

        $this->postJson(route('employee-salaries.store'), [
                'employee_id' => $employeeId,
                'salary_month' => now()->month,
                'salary_year' => now()->year,
                'working_days' => 19.99,
                'bonus_amount' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bonus_amount');
    }

    public function test_admin_can_update_compliance_settings(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->put(route('employee-compliance-settings.update'), [
                'epf_employee_percentage' => 10,
                'epf_employer_percentage' => 12,
                'esi_employee_percentage' => 1,
                'esi_employer_percentage' => 2,
                'esi_eligibility_amount' => 21000,
                'epf_eligibility_amount' => 15000,
                'bonus_eligibility_amount' => 21000,
            ])
            ->assertRedirect(route('employee-compliance-settings.index'));

        $this->assertDatabaseHas('employee_compliance_settings', [
            'id' => 1,
            'esi_eligibility_amount' => 21000,
            'epf_eligibility_amount' => 15000,
            'bonus_eligibility_amount' => 21000,
        ]);
    }

    public function test_salary_and_compliance_settings_pages_render(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        DB::table('employees')->insert([
            'employee_code' => 'EMP-003',
            'first_name' => 'Employee',
            'designation' => 'Technician',
            'work_location' => 'North Site',
            'wages_rate_per_day' => 125,
            'emp_type' => 1,
        ]);
        DB::table('contracts')->insert([
            'employee_id' => 1,
            'contract_number' => 'CON-003',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('employee_salaries')->insert([
            'employee_id' => 1,
            'basic_salary' => 0,
            'gross_salary' => 2500,
            'net_salary' => 2300,
            'effective_from' => now()->format('Y-m-01'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('employee-salaries.index'))
            ->assertOk()
            ->assertSee('CON-003')
            ->assertSee('125.00')
            ->assertSee('Working Days')
            ->assertSee('name="salary_month"', false)
            ->assertSee('name="salary_year"', false)
            ->assertSee('id="filter_month"', false)
            ->assertSee('<option value="all" selected>', false)
            ->assertSee('id="filter_year"', false)
            ->assertSee('Daily Wages')
            ->assertSee('Contract Number')
            ->assertSee('id="view_salary_contract_number"', false)
            ->assertSee('id="view_salary_daily_wage"', false)
            ->assertSee('Employee Category')
            ->assertSee('Designation')
            ->assertSee('Work Location')
            ->assertSee('Bonus Amount Given')
            ->assertSee('data-daily-wage=', false)
            ->assertSee('data-emp-type="1"', false)
            ->assertSee('data-designation=', false)
            ->assertSee('Technician')
            ->assertSee('data-work-location=', false)
            ->assertSee('North Site')
            ->assertSee('data-contract-number="CON-003"', false)
            ->assertSee('id="salary_paid_holidays_amount"', false)
            ->assertSee('id="salary_leave_with_wages_amount"', false)
            ->assertSee(route('employee-salaries.export'))
            ->assertSee('Select salary rows to export only those records.')
            ->assertSee('id="selectAllSalaryRows"', false)
            ->assertSee('class="form-check-input salary-export-checkbox"', false)
            ->assertDontSee('Export PDF')
            ->assertDontSee('Basic Salary')
            ->assertDontSee('Allowances');

        $this->get(route('employee-compliance-settings.index'))
            ->assertOk()
            ->assertSee('EPF Employee Share');
        $this->get(route('employee-compliance-settings.index'))
            ->assertOk()
            ->assertSee('ESI Eligibility Amount');
    }

    public function test_salary_employee_autofill_uses_assigned_contract(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-ASSIGNED',
            'first_name' => 'Assigned',
            'last_name' => 'Employee',
            'wages_rate_per_day' => 150,
        ]);
        $contractId = DB::table('contracts')->insertGetId([
            'employee_id' => null,
            'contract_number' => 'CON-ASSIGNED',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('contract_service_assignments')->insert([
            'contract_id' => $contractId,
            'employee_id' => $employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('employee-salaries.index'))
            ->assertOk()
            ->assertSee('data-contract-number="CON-ASSIGNED"', false);
    }

    public function test_salary_export_downloads_full_filtered_or_selected_excel_rows(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-004',
            'first_name' => 'Export',
            'last_name' => 'Employee',
            'designation' => 'Electrician',
            'emp_type' => 3,
            'wages_rate_per_day' => 150,
        ]);
        DB::table('contracts')->insert([
            'employee_id' => $employeeId,
            'contract_number' => 'CON-004',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('employee_salaries')->insert([
            'employee_id' => $employeeId,
            'basic_salary' => 0,
            'gross_salary' => 3000,
            'net_salary' => 2800,
            'effective_from' => now()->format('Y-m-01'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('employee_salaries')->insert([
            'employee_id' => $employeeId,
            'basic_salary' => 0,
            'gross_salary' => 3500,
            'net_salary' => 3300,
            'effective_from' => now()->format('Y-m-01'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin);
        $excelResponse = $this->get(route('employee-salaries.export', ['employee_id' => $employeeId]))
            ->assertOk()
            ->assertDownload('employee-salaries.xlsx')
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
        $excelPath = $excelResponse->baseResponse->getFile()->getPathname();
        $archive = new \ZipArchive();
        $this->assertSame(true, $archive->open($excelPath));
        $sheet = $archive->getFromName('xl/worksheets/sheet1.xml');
        $this->assertIsString($sheet);
        $this->assertStringContainsString('CON-004', $sheet);
        $this->assertStringContainsString('Designation', $sheet);
        $this->assertStringContainsString('Employee Category', $sheet);
        $this->assertStringContainsString('Salary Month', $sheet);
        $this->assertStringContainsString(now()->format('F'), $sheet);
        $this->assertStringContainsString('Electrician', $sheet);
        $this->assertStringContainsString('Highly Skilled', $sheet);
        $this->assertStringContainsString('Net Salary', $sheet);
        $this->assertStringContainsString('<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>', $sheet);
        $this->assertStringContainsString('<c r="A1" s="1"', $sheet);
        $this->assertStringContainsString('3500', $sheet);
        $this->assertStringContainsString('3000', $sheet);
        $styles = $archive->getFromName('xl/styles.xml');
        $this->assertIsString($styles);
        $this->assertStringContainsString('<b/>', $styles);
        $this->assertStringContainsString('<color rgb="FFFFFFFF"/>', $styles);
        $this->assertStringContainsString('<fgColor rgb="FF1F2937"/>', $styles);
        $archive->close();
        unlink($excelPath);

        $selectedResponse = $this->get(route('employee-salaries.export', [
            'selected_ids' => [1],
            'employee_id' => $employeeId,
        ]))->assertOk()->assertDownload('employee-salaries.xlsx');
        $selectedPath = $selectedResponse->baseResponse->getFile()->getPathname();
        $selectedArchive = new \ZipArchive();
        $this->assertSame(true, $selectedArchive->open($selectedPath));
        $selectedSheet = $selectedArchive->getFromName('xl/worksheets/sheet1.xml');
        $this->assertIsString($selectedSheet);
        $this->assertStringContainsString('3000', $selectedSheet);
        $this->assertStringNotContainsString('3500', $selectedSheet);
        $selectedArchive->close();
        unlink($selectedPath);
    }

    public function test_salary_month_and_year_filters_select_the_requested_period(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-008',
            'first_name' => 'Period',
            'wages_rate_per_day' => 100,
        ]);
        foreach ([
            [now()->startOfMonth()->toDateString(), 1000],
            [now()->startOfMonth()->subMonthNoOverflow()->toDateString(), 500],
        ] as [$period, $salary]) {
            DB::table('employee_salaries')->insert([
                'employee_id' => $employeeId,
                'basic_salary' => 0,
                'gross_salary' => $salary,
                'net_salary' => $salary,
                'effective_from' => $period,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('employee-salaries.index', [
                'month' => now()->month,
                'year' => now()->year,
            ]))
            ->assertOk()
            ->assertSee('1,000.00')
            ->assertDontSee('500.00');
    }

    public function test_salary_list_shows_all_months_by_default(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-009',
            'first_name' => 'All Months',
            'wages_rate_per_day' => 100,
        ]);
        foreach ([
            [now()->startOfMonth()->toDateString(), 1000],
            [now()->startOfMonth()->subMonthNoOverflow()->toDateString(), 500],
        ] as [$period, $salary]) {
            DB::table('employee_salaries')->insert([
                'employee_id' => $employeeId,
                'basic_salary' => 0,
                'gross_salary' => $salary,
                'net_salary' => $salary,
                'effective_from' => $period,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('employee-salaries.index'))
            ->assertOk()
            ->assertSee('1,000.00')
            ->assertSee('500.00')
            ->assertSee('<option value="all" selected>', false);
    }
}
