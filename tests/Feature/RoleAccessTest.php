<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class RoleAccessTest extends TestCase
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
    }

    public function test_public_registration_creates_a_read_only_sub_admin(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => User::ROLE_SUB_ADMIN,
        ]);
    }

    public function test_dashboard_displays_the_signed_in_users_role(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Admin');
    }

    public function test_only_super_admin_can_open_user_management_and_create_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Another User',
                'email' => 'another@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => User::ROLE_ADMIN,
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_create_records_and_sub_admin_cannot_mutate_records(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $subAdmin = User::factory()->create(['role' => User::ROLE_SUB_ADMIN]);

        $this->actingAs($admin)
            ->post('/employees', [])
            ->assertForbidden();

        $this->actingAs($subAdmin)
            ->put('/employees/1', [])
            ->assertForbidden();

        $this->actingAs($subAdmin)
            ->delete('/employee-salaries/1')
            ->assertForbidden();
    }

    public function test_super_admin_can_create_users_with_selected_roles(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Create User');

        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'name' => 'Manager',
                'email' => 'manager@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => User::ROLE_ADMIN,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'manager@example.com',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_super_admin_can_update_another_users_role_but_not_their_own(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($superAdmin)
            ->patch(route('users.role.update', $admin), ['role' => User::ROLE_SUB_ADMIN])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => User::ROLE_SUB_ADMIN,
        ]);

        $this->patch(route('users.role.update', $superAdmin), ['role' => User::ROLE_ADMIN])
            ->assertForbidden();
    }
}
