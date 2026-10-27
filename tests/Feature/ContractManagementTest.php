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
        ]);

        $this->deleteJson(route('contracts.destroy', $contractId))
            ->assertOk()
            ->assertJson(['message' => 'Contract deleted successfully.']);

        $this->assertDatabaseMissing('contracts', ['id' => $contractId]);
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
