<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractManagementTest extends TestCase
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

        Schema::connection('sqlite')->create('employees', function ($table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('status')->default('active');
        });

        $roleMigration = require base_path('database/migrations/2026_10_27_000000_add_role_to_users_table.php');
        $roleMigration->up();
        $contractMigration = require base_path('database/migrations/2026_10_27_000004_create_contracts_table.php');
        $contractMigration->up();
        $contractDetailsMigration = require base_path('database/migrations/2026_10_28_000001_update_contract_details_and_make_employee_optional.php');
        $contractDetailsMigration->up();
        $serviceVehicleMigration = require base_path('database/migrations/2026_10_29_000000_create_service_vehicles_table.php');
        $serviceVehicleMigration->up();
        $serviceAssignmentsMigration = require base_path('database/migrations/2026_10_29_000001_create_contract_service_assignments_table.php');
        $serviceAssignmentsMigration->up();
        $removeContractTitleMigration = require base_path('database/migrations/2026_10_29_000002_remove_title_from_contracts_table.php');
        $removeContractTitleMigration->up();
        $holidayOptionsMigration = require base_path('database/migrations/2026_10_29_000003_add_holiday_options_to_contracts_table.php');
        $holidayOptionsMigration->up();
        $contractExtensionMigration = require base_path('database/migrations/2026_10_31_000000_add_extension_date_and_holiday_percentages_to_contracts_table.php');
        $contractExtensionMigration->up();
    }

    public function test_super_admin_can_create_update_list_and_delete_contracts(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-001',
            'first_name' => 'Taylor',
            'last_name' => 'Example',
        ]);
        $this->actingAs($user);

        $contractData = [
            'contract_number' => 'CON-001',
            'employee_id' => $employeeId,
            'department' => 'Operations',
            'organization' => 'Example Organization',
            'address' => '1 Example Street',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'extension_date' => '2027-03-31',
            'amount' => '25000.00',
            'paid_holidays' => '1',
            'paid_holidays_percentage' => '8.50',
            'leave_with_wages' => '1',
            'leave_with_wages_percentage' => '4.25',
            'status' => 'active',
            'notes' => 'Initial agreement',
        ];

        $this->postJson(route('contracts.store'), $contractData)
            ->assertOk()
            ->assertJson(['message' => 'Contract added successfully.']);

        $contractId = DB::table('contracts')->value('id');
        $this->get(route('contracts.index'))
            ->assertOk()
            ->assertSee('CON-001')
            ->assertDontSee('Employee</th>', false)
            ->assertDontSee('name="title"', false)
            ->assertDontSee('Taylor Example');

        $this->putJson(route('contracts.update', $contractId), [
            ...$contractData,
        ])->assertOk()
            ->assertJson(['message' => 'Contract updated successfully.']);

        $this->assertDatabaseHas('contracts', [
            'id' => $contractId,
            'department' => 'Operations',
            'organization' => 'Example Organization',
            'address' => '1 Example Street',
            'contract_type' => 'Manpower',
            'paid_holidays' => 1,
            'paid_holidays_percentage' => 8.5,
            'leave_with_wages' => 1,
            'leave_with_wages_percentage' => 4.25,
            'extension_date' => '2027-03-31',
        ]);

        $this->deleteJson(route('contracts.destroy', $contractId))
            ->assertOk()
            ->assertJson(['message' => 'Contract deleted successfully.']);

        $this->assertDatabaseMissing('contracts', ['id' => $contractId]);
    }

    public function test_assign_action_is_hidden_for_completed_and_terminated_contracts(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($user);

        $contractIds = [];
        foreach (['draft', 'active', 'completed', 'terminated'] as $status) {
            $contractIds[$status] = DB::table('contracts')->insertGetId([
                'contract_number' => 'CON-' . strtoupper($status),
                'start_date' => '2026-01-01',
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->get(route('contracts.index'))->assertOk();

        foreach (['draft', 'active'] as $status) {
            $response->assertSee(
                route('contracts.services.edit', [$contractIds[$status], 'modal' => 1]),
                false
            );
        }
        foreach (['completed', 'terminated'] as $status) {
            $response->assertDontSee(
                route('contracts.services.edit', [$contractIds[$status], 'modal' => 1]),
                false
            );
        }
    }

    public function test_contract_can_be_created_without_employee_and_form_has_requested_fields(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($user);

        $this->get(route('contracts.index'))
            ->assertOk()
            ->assertDontSee('name="employee_id"', false)
            ->assertDontSee('name="title"', false)
            ->assertSee('name="paid_holidays"', false)
            ->assertSee('name="leave_with_wages"', false)
            ->assertSee('id="paid_holidays" value="1"', false)
            ->assertSee('id="leave_with_wages" value="1"', false)
            ->assertSee('name="extension_date"', false)
            ->assertSee('name="paid_holidays_percentage"', false)
            ->assertSee('name="leave_with_wages_percentage"', false)
            ->assertSee('id="extensionDateField"', false)
            ->assertSee('Assigned Services')
            ->assertSee('view-contract-btn')
            ->assertSee('name="department"', false)
            ->assertSee('name="organization"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="contract_type"', false)
            ->assertSee('contract-form-body', false);

        $this->postJson(route('contracts.store'), [
            'contract_number' => 'CON-002',
            'contract_type' => 'Ambulance',
            'department' => 'Emergency Services',
            'organization' => 'City Health',
            'address' => '2 Health Road',
            'paid_holidays' => 0,
            'leave_with_wages' => 0,
            'start_date' => '2026-01-01',
            'status' => 'draft',
        ])->assertOk();

        $this->assertDatabaseHas('contracts', [
            'contract_number' => 'CON-002',
            'employee_id' => null,
            'contract_type' => 'Ambulance',
            'department' => 'Emergency Services',
            'organization' => 'City Health',
            'address' => '2 Health Road',
        ]);
    }

    public function test_enabled_holiday_options_require_a_percentage_and_disabled_values_are_cleared(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($user);

        $contractData = [
            'contract_number' => 'CON-003',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'draft',
            'paid_holidays' => 1,
            'leave_with_wages' => 0,
        ];

        $this->postJson(route('contracts.store'), $contractData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('paid_holidays_percentage');

        $this->postJson(route('contracts.store'), [
            ...$contractData,
            'paid_holidays_percentage' => '5.5',
            'leave_with_wages_percentage' => '25',
        ])->assertOk();

        $this->assertDatabaseHas('contracts', [
            'contract_number' => 'CON-003',
            'paid_holidays_percentage' => 5.5,
            'leave_with_wages_percentage' => null,
        ]);
    }

    public function test_completed_contract_resources_are_available_for_assignment_and_cannot_be_reactivated_after_reassignment(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-FREE-001',
            'first_name' => 'Available',
            'last_name' => 'Worker',
        ]);
        $completedContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-FREE-001',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $targetContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-FREE-002',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('contract_service_assignments')->insert([
            'contract_id' => $completedContractId,
            'employee_id' => $employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->putJson(route('contracts.update', $completedContractId), [
            'contract_number' => 'CON-FREE-001',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'completed',
        ])->assertOk();

        $this->get(route('contracts.services.edit', $targetContractId))
            ->assertOk()
            ->assertSee('EMP-FREE-001')
            ->assertSee('Available Worker');

        $this->put(route('contracts.services.update', $targetContractId), [
            'assignments' => [['employee_id' => $employeeId]],
        ])->assertRedirect(route('contracts.services.edit', $targetContractId));

        $this->putJson(route('contracts.update', $completedContractId), [
            'contract_number' => 'CON-FREE-001',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
        ])->assertJsonValidationErrors('status');
    }

    public function test_resources_can_be_moved_out_of_a_terminated_contract(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-MOVE-001',
            'first_name' => 'Moving',
            'last_name' => 'Worker',
        ]);
        $sourceContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-MOVE-001',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $targetContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-MOVE-002',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $assignmentId = DB::table('contract_service_assignments')->insertGetId([
            'contract_id' => $sourceContractId,
            'employee_id' => $employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->putJson(route('contracts.update', $sourceContractId), [
            'contract_number' => 'CON-MOVE-001',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'terminated',
        ])->assertOk();

        $this->get(route('contracts.services.edit', $sourceContractId))
            ->assertOk()
            ->assertSee('resources are available for other contracts')
            ->assertDontSee('id="serviceAssignmentsForm"', false)
            ->assertSee('move-resource-btn');

        $this->post(route('contracts.services.move', [$sourceContractId, $assignmentId]), [
            'resource' => 'employee',
            'target_contract_id' => $targetContractId,
            'target_role' => 'employee',
        ])->assertRedirect(route('contracts.services.edit', $targetContractId));

        $this->assertDatabaseMissing('contract_service_assignments', [
            'contract_id' => $sourceContractId,
            'employee_id' => $employeeId,
        ]);
        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $targetContractId,
            'employee_id' => $employeeId,
        ]);
    }

    public function test_contract_mutations_use_the_existing_record_permissions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)
            ->postJson(route('contracts.store'), [])
            ->assertForbidden();

        $subAdmin = User::factory()->create(['role' => User::ROLE_SUB_ADMIN]);
        $this->actingAs($subAdmin)
            ->putJson(route('contracts.update', 1), [])
            ->assertForbidden();

        $this->post(route('contracts.services.move', [1, 1]), ['target_contract_id' => 2])
            ->assertForbidden();
    }

    public function test_manpower_employees_can_be_assigned_to_a_contract_and_seen_in_the_listing(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-002',
            'first_name' => 'Jordan',
            'last_name' => 'Worker',
        ]);
        $contractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-003',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get(route('contracts.services.edit', $contractId))
            ->assertOk()
            ->assertSee('Contract Manpower');
        $this->get(route('contracts.services.edit', [$contractId, 'modal' => 1]))
            ->assertOk()
            ->assertSee('Contract Manpower')
            ->assertDontSee('ANSH Enterprises')
            ->assertDontSee('sidebar');

        $this->put(route('contracts.services.update', $contractId), [
            'assignments' => [
                ['employee_id' => $employeeId],
            ],
        ])->assertRedirect(route('contracts.services.edit', $contractId));

        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $contractId,
            'employee_id' => $employeeId,
            'service_vehicle_id' => null,
        ]);

        $this->get(route('contracts.index'))
            ->assertOk()
            ->assertSee('Assigned Services')
            ->assertSee('Jordan%20Worker')
            ->assertSee('view-contract-btn');

        $this->putJson(route('contracts.update', $contractId), [
            'contract_number' => 'CON-003',
            'contract_type' => 'Ambulance',
            'start_date' => '2026-01-01',
            'status' => 'active',
        ])->assertOk();

        $this->assertDatabaseMissing('contract_service_assignments', [
            'contract_id' => $contractId,
        ]);
    }

    public function test_ambulance_contract_can_be_assigned_a_registered_vehicle_driver_and_optional_assistant(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $driverId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-003',
            'first_name' => 'Casey',
            'last_name' => 'Driver',
        ]);
        $assistantId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-004',
            'first_name' => 'Sam',
            'last_name' => 'Assistant',
        ]);
        $contractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-004',
            'contract_type' => 'Ambulance',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('service_vehicles')->insertGetId([
            'vehicle_type' => 'Ambulance',
            'registration_number' => 'AMB-001',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->put(route('contracts.services.update', $contractId), [
            'assignments' => [[
                'service_vehicle_id' => $vehicleId,
                'driver_id' => $driverId,
                'assistant_id' => $assistantId,
            ]],
        ])->assertRedirect(route('contracts.services.edit', $contractId));

        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $contractId,
            'service_vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'assistant_id' => $assistantId,
        ]);

        $this->get(route('contracts.index'))
            ->assertOk()
            ->assertSee('CON-004')
            ->assertSee('Assigned Services')
            ->assertSee('AMB-001')
            ->assertSee('Casey%20Driver')
            ->assertSee('view-contract-btn');
    }

    public function test_vehicle_registry_rejects_assigning_the_wrong_vehicle_type(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $driverId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-005',
            'first_name' => 'Taylor',
            'last_name' => 'Driver',
        ]);
        $contractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-005',
            'contract_type' => 'Cabs',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('service_vehicles')->insertGetId([
            'vehicle_type' => 'Ambulance',
            'registration_number' => 'AMB-002',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->post(route('service-vehicles.store'), [
            'vehicle_type' => 'Cabs',
            'registration_number' => 'CAB-001',
            'make_model' => 'Sedan',
            'status' => 'active',
        ])->assertRedirect(route('service-vehicles.index'));

        $this->put(route('contracts.services.update', $contractId), [
            'assignments' => [[
                'service_vehicle_id' => $vehicleId,
                'driver_id' => $driverId,
            ]],
        ])->assertSessionHasErrors('assignments');

        $this->assertDatabaseMissing('contract_service_assignments', [
            'contract_id' => $contractId,
        ]);
        $this->get(route('service-vehicles.index'))
            ->assertOk()
            ->assertSee('CAB-001')
            ->assertSee('AMB-002');
    }

    public function test_contracts_can_have_multiple_manpower_employees_and_multiple_service_vehicles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeIds = [];
        foreach ([
            ['EMP-006', 'Alex', 'One'],
            ['EMP-007', 'Jamie', 'Two'],
            ['EMP-008', 'Morgan', 'Three'],
            ['EMP-009', 'Riley', 'Four'],
        ] as [$code, $firstName, $lastName]) {
            $employeeIds[] = DB::table('employees')->insertGetId([
                'employee_code' => $code,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        }

        $manpowerContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-006',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cabContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-007',
            'contract_type' => 'Cabs',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleIds = [];
        foreach (['CAB-002', 'CAB-003'] as $registration) {
            $vehicleIds[] = DB::table('service_vehicles')->insertGetId([
                'vehicle_type' => 'Cabs',
                'registration_number' => $registration,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($user);

        $this->put(route('contracts.services.update', $manpowerContractId), [
            'assignments' => [
                ['employee_id' => $employeeIds[0]],
                ['employee_id' => $employeeIds[1]],
            ],
        ])->assertRedirect(route('contracts.services.edit', $manpowerContractId));

        $this->put(route('contracts.services.update', $cabContractId), [
            'assignments' => [
                ['service_vehicle_id' => $vehicleIds[0], 'driver_id' => $employeeIds[2]],
                ['service_vehicle_id' => $vehicleIds[1], 'driver_id' => $employeeIds[3]],
            ],
        ])->assertRedirect(route('contracts.services.edit', $cabContractId));

        $this->assertSame(
            2,
            DB::table('contract_service_assignments')->where('contract_id', $manpowerContractId)->count()
        );
        $this->assertSame(
            2,
            DB::table('contract_service_assignments')->where('contract_id', $cabContractId)->count()
        );
        $this->get(route('contracts.index'))
            ->assertOk()
            ->assertSee('Assigned Services')
            ->assertSee('Alex%20One')
            ->assertSee('view-contract-btn');
    }

    public function test_contract_number_check_reports_duplicates_and_allows_the_current_contract_during_edit(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $contractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-008',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->getJson(route('contracts.check-number', ['contract_number' => 'CON-008']))
            ->assertOk()
            ->assertJson(['available' => false]);

        $this->getJson(route('contracts.check-number', [
            'contract_number' => 'CON-008',
            'exclude_id' => $contractId,
        ]))->assertOk()
            ->assertJson(['available' => true]);

        $this->getJson(route('contracts.check-number', ['contract_number' => 'CON-009']))
            ->assertOk()
            ->assertJson(['available' => true]);

        $this->postJson(route('contracts.store'), [
            'contract_number' => 'CON-008',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'draft',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('contract_number');
    }

    public function test_employees_and_vehicles_assigned_to_other_contracts_are_hidden_and_rejected(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $assignedEmployeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-010',
            'first_name' => 'Already',
            'last_name' => 'Assigned',
        ]);
        $availableEmployeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-011',
            'first_name' => 'Still',
            'last_name' => 'Available',
        ]);
        $firstContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-009',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $secondContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-010',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('contract_service_assignments')->insert([
            'contract_id' => $firstContractId,
            'employee_id' => $assignedEmployeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get(route('contracts.services.edit', $secondContractId))
            ->assertOk()
            ->assertDontSee('EMP-010')
            ->assertSee('EMP-011');

        $this->put(route('contracts.services.update', $secondContractId), [
            'assignments' => [
                ['employee_id' => $assignedEmployeeId],
            ],
        ])->assertSessionHasErrors('assignments.0.employee_id');

        $this->assertDatabaseMissing('contract_service_assignments', [
            'contract_id' => $secondContractId,
        ]);

        $firstCabContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-011',
            'contract_type' => 'Cabs',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $secondCabContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-012',
            'contract_type' => 'Cabs',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('service_vehicles')->insertGetId([
            'vehicle_type' => 'Cabs',
            'registration_number' => 'CAB-004',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('contract_service_assignments')->insert([
            'contract_id' => $firstCabContractId,
            'service_vehicle_id' => $vehicleId,
            'driver_id' => $availableEmployeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('contracts.services.edit', $secondCabContractId))
            ->assertOk()
            ->assertDontSee('CAB-004')
            ->assertDontSee('EMP-011');

        $this->put(route('contracts.services.update', $secondCabContractId), [
            'assignments' => [[
                'service_vehicle_id' => $vehicleId,
                'driver_id' => $availableEmployeeId,
            ]],
        ])->assertSessionHasErrors([
            'assignments.0.service_vehicle_id',
            'assignments.0.driver_id',
        ]);
        $this->assertDatabaseMissing('contract_service_assignments', [
            'contract_id' => $secondCabContractId,
        ]);
    }

    public function test_manpower_employee_can_be_moved_between_same_type_contracts(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $employeeId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-012',
            'first_name' => 'Move',
            'last_name' => 'Worker',
        ]);
        $sourceContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-013',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $targetContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-014',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $assignmentId = DB::table('contract_service_assignments')->insertGetId([
            'contract_id' => $sourceContractId,
            'employee_id' => $employeeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get(route('contracts.services.edit', $sourceContractId))
            ->assertOk()
            ->assertSee('Move employee');
        $this->get(route('contracts.services.move.edit', [
            $sourceContractId,
            $assignmentId,
            'resource' => 'employee',
            'modal' => 1,
        ]))->assertOk()
            ->assertSee('CON-014')
            ->assertDontSee('ANSH Enterprises')
            ->assertDontSee('sidebar');

        $this->post(route('contracts.services.move', [$sourceContractId, $assignmentId]), [
            'resource' => 'employee',
            'target_contract_id' => $targetContractId,
            'target_role' => 'employee',
        ])->assertRedirect(route('contracts.services.edit', $targetContractId))
            ->assertSessionHas('status', 'Service resource moved successfully.');

        $this->assertDatabaseMissing('contract_service_assignments', [
            'id' => $assignmentId,
            'contract_id' => $sourceContractId,
        ]);
        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $targetContractId,
            'employee_id' => $employeeId,
        ]);
    }

    public function test_vehicle_moves_without_its_crew_and_different_service_type_is_rejected(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $driverId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-013',
            'first_name' => 'Move',
            'last_name' => 'Driver',
        ]);
        $assistantId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-014',
            'first_name' => 'Move',
            'last_name' => 'Assistant',
        ]);
        $sourceContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-015',
            'contract_type' => 'Ambulance',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $targetContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-016',
            'contract_type' => 'Ambulance',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $wrongTypeContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-017',
            'contract_type' => 'Cabs',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('service_vehicles')->insertGetId([
            'vehicle_type' => 'Ambulance',
            'registration_number' => 'AMB-003',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $assignmentId = DB::table('contract_service_assignments')->insertGetId([
            'contract_id' => $sourceContractId,
            'service_vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'assistant_id' => $assistantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->post(route('contracts.services.move', [$sourceContractId, $assignmentId]), [
            'resource' => 'vehicle',
            'target_contract_id' => $wrongTypeContractId,
        ])->assertSessionHasErrors('target_contract_id');

        $this->assertDatabaseHas('contract_service_assignments', [
            'id' => $assignmentId,
            'contract_id' => $sourceContractId,
        ]);

        $this->post(route('contracts.services.move', [$sourceContractId, $assignmentId]), [
            'resource' => 'vehicle',
            'target_contract_id' => $targetContractId,
        ])->assertRedirect(route('contracts.services.edit', $targetContractId));

        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $targetContractId,
            'service_vehicle_id' => $vehicleId,
            'driver_id' => null,
            'assistant_id' => null,
        ]);
        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $sourceContractId,
            'service_vehicle_id' => null,
            'driver_id' => $driverId,
            'assistant_id' => $assistantId,
        ]);
        $this->get(route('contracts.services.edit', $sourceContractId))
            ->assertOk()
            ->assertSee('Move Driver')
            ->assertSee('Move Assistant');
        $this->put(route('contracts.services.update', $sourceContractId), [
            'assignments' => [[
                'service_vehicle_id' => '',
                'driver_id' => $driverId,
                'assistant_id' => $assistantId,
            ]],
        ])->assertRedirect(route('contracts.services.edit', $sourceContractId));
        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $sourceContractId,
            'service_vehicle_id' => null,
            'driver_id' => $driverId,
            'assistant_id' => $assistantId,
        ]);
    }

    public function test_driver_and_assistant_can_move_independently_to_other_contract_types(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $driverId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-015',
            'first_name' => 'Independent',
            'last_name' => 'Driver',
        ]);
        $assistantId = DB::table('employees')->insertGetId([
            'employee_code' => 'EMP-016',
            'first_name' => 'Independent',
            'last_name' => 'Assistant',
        ]);
        $sourceContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-018',
            'contract_type' => 'Ambulance',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cabContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-019',
            'contract_type' => 'Cabs',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $manpowerContractId = DB::table('contracts')->insertGetId([
            'contract_number' => 'CON-020',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $ambulanceId = DB::table('service_vehicles')->insertGetId([
            'vehicle_type' => 'Ambulance',
            'registration_number' => 'AMB-004',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cabId = DB::table('service_vehicles')->insertGetId([
            'vehicle_type' => 'Cabs',
            'registration_number' => 'CAB-005',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sourceAssignmentId = DB::table('contract_service_assignments')->insertGetId([
            'contract_id' => $sourceContractId,
            'service_vehicle_id' => $ambulanceId,
            'driver_id' => $driverId,
            'assistant_id' => $assistantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cabAssignmentId = DB::table('contract_service_assignments')->insertGetId([
            'contract_id' => $cabContractId,
            'service_vehicle_id' => $cabId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs($user);

        $this->post(route('contracts.services.move', [$sourceContractId, $sourceAssignmentId]), [
            'resource' => 'driver',
            'target_contract_id' => $cabContractId,
            'target_role' => 'driver',
            'target_assignment_id' => $cabAssignmentId,
        ])->assertRedirect(route('contracts.services.edit', $cabContractId));

        $this->post(route('contracts.services.move', [$sourceContractId, $sourceAssignmentId]), [
            'resource' => 'assistant',
            'target_contract_id' => $manpowerContractId,
            'target_role' => 'employee',
        ])->assertRedirect(route('contracts.services.edit', $manpowerContractId));

        $this->assertDatabaseHas('contract_service_assignments', [
            'id' => $sourceAssignmentId,
            'service_vehicle_id' => $ambulanceId,
            'driver_id' => null,
            'assistant_id' => null,
        ]);
        $this->assertDatabaseHas('contract_service_assignments', [
            'id' => $cabAssignmentId,
            'driver_id' => $driverId,
        ]);
        $this->assertDatabaseHas('contract_service_assignments', [
            'contract_id' => $manpowerContractId,
            'employee_id' => $assistantId,
        ]);
    }
}
