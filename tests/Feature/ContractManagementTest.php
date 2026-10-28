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
        });

        $roleMigration = require base_path('database/migrations/2026_10_27_000000_add_role_to_users_table.php');
        $roleMigration->up();
        $contractMigration = require base_path('database/migrations/2026_10_27_000004_create_contracts_table.php');
        $contractMigration->up();
        $contractDetailsMigration = require base_path('database/migrations/2026_10_28_000001_update_contract_details_and_make_employee_optional.php');
        $contractDetailsMigration->up();
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
            'title' => 'Employment Agreement',
            'department' => 'Operations',
            'organization' => 'Example Organization',
            'address' => '1 Example Street',
            'contract_type' => 'Manpower',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'amount' => '25000.00',
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
            ->assertSee('Taylor Example');

        $this->putJson(route('contracts.update', $contractId), [
            ...$contractData,
            'title' => 'Updated Agreement',
        ])->assertOk()
            ->assertJson(['message' => 'Contract updated successfully.']);

        $this->assertDatabaseHas('contracts', [
            'id' => $contractId,
            'title' => 'Updated Agreement',
            'department' => 'Operations',
            'organization' => 'Example Organization',
            'address' => '1 Example Street',
            'contract_type' => 'Manpower',
        ]);

        $this->deleteJson(route('contracts.destroy', $contractId))
            ->assertOk()
            ->assertJson(['message' => 'Contract deleted successfully.']);

        $this->assertDatabaseMissing('contracts', ['id' => $contractId]);
    }

    public function test_contract_can_be_created_without_employee_and_form_has_requested_fields(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($user);

        $this->get(route('contracts.index'))
            ->assertOk()
            ->assertDontSee('name="employee_id"', false)
            ->assertSee('name="department"', false)
            ->assertSee('name="organization"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="contract_type"', false)
            ->assertSee('contract-form-body', false);

        $this->postJson(route('contracts.store'), [
            'contract_number' => 'CON-002',
            'title' => 'Ambulance Service',
            'contract_type' => 'Ambulance',
            'department' => 'Emergency Services',
            'organization' => 'City Health',
            'address' => '2 Health Road',
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
    }
}
