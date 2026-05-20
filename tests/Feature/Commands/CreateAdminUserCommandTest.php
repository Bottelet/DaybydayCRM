<?php

namespace Tests\Feature\Commands;

use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\AbstractTestCase;

class CreateAdminUserCommandTest extends AbstractTestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Task 3.1: Fresh database scenarios
    // ──────────────────────────────────────────────

    /**
     * Test that the command successfully creates an admin user on a completely unseeded database.
     */
    public function test_command_creates_admin_user_on_fresh_database(): void
    {
        // Start with a fresh, unseeded database
        $this->artisan('migrate:fresh')->run();

        $email = 'admin@example.com';

        $this->artisan('user:create-admin', [
            '--name'     => 'Admin User',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('Admin User', $user->name);
        $this->assertTrue(Hash::check('SecureP@ssw0rd', $user->password));
    }

    /**
     * Test that all dependencies are auto-provisioned on a fresh database.
     */
    public function test_command_auto_provisions_dependencies_on_fresh_database(): void
    {
        $this->artisan('migrate:fresh')->run();

        // Verify database is empty
        $this->assertCount(0, Setting::all());
        $this->assertCount(0, Role::all());
        $this->assertCount(0, Department::all());

        $this->artisan('user:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => 'admin@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        // Verify all dependencies exist
        $this->assertCount(1, Setting::all());
        $this->assertTrue(Role::where('name', 'owner')->exists());
        $this->assertTrue(Department::where('name', 'Management')->exists());
    }

    /**
     * Test that created user has correct attributes.
     */
    public function test_created_user_has_correct_attributes(): void
    {
        $this->artisan('migrate:fresh')->run();

        $email    = 'admin@example.com';
        $name     = 'John Doe';
        $password = 'TestP@ss123';

        $this->artisan('user:create-admin', [
            '--name'     => $name,
            '--email'    => $email,
            '--password' => $password,
        ])->assertExitCode(0);

        $user = User::where('email', $email)->first();

        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotNull($user->external_id);
        $this->assertEquals('en', $user->language);
    }

    // ──────────────────────────────────────────────
    // Task 3.2: Seeded database compatibility
    // ──────────────────────────────────────────────

    /**
     * Test successful admin creation after running DatabaseSeeder.
     */
    public function test_command_creates_admin_on_seeded_database(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $email = 'newadmin@example.com';

        $this->artisan('user:create-admin', [
            '--name'     => 'New Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
    }

    /**
     * Test that firstOrCreate logic reuses existing settings, role, and department.
     */
    public function test_command_reuses_existing_dependencies(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        // Count records before
        $settingsCountBefore   = Setting::count();
        $roleCountBefore       = Role::count();
        $departmentCountBefore = Department::count();

        // Create first admin
        $this->artisan('user:create-admin', [
            '--name'     => 'First Admin',
            '--email'    => 'first@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        // Create second admin
        $this->artisan('user:create-admin', [
            '--name'     => 'Second Admin',
            '--email'    => 'second@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        // Verify no duplicates created
        $this->assertEquals($settingsCountBefore, Setting::count());
        $this->assertEquals($roleCountBefore, Role::count());
        $this->assertEquals($departmentCountBefore, Department::count());
    }

    /**
     * Test that record counts remain unchanged when dependencies already exist.
     */
    public function test_no_duplicate_records_on_multiple_runs(): void
    {
        $this->artisan('migrate:fresh')->run();

        // First run
        $this->artisan('user:create-admin', [
            '--name'     => 'Admin One',
            '--email'    => 'admin1@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $settingsCount   = Setting::count();
        $roleCount       = Role::count();
        $departmentCount = Department::count();

        // Second run
        $this->artisan('user:create-admin', [
            '--name'     => 'Admin Two',
            '--email'    => 'admin2@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        // Counts remain the same
        $this->assertEquals($settingsCount, Setting::count());
        $this->assertEquals($roleCount, Role::count());
        $this->assertEquals($departmentCount, Department::count());

        // But we have 2 users
        $this->assertEquals(2, User::count());
    }

    // ──────────────────────────────────────────────
    // Task 3.3: Error handling
    // ──────────────────────────────────────────────

    /**
     * Test that attempting to create a user with an already-existing email fails.
     */
    public function test_command_fails_with_duplicate_email(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $email = 'duplicate@example.com';

        // Create first user
        User::factory()->create(['email' => $email]);

        // Attempt to create admin with same email
        $this->artisan('user:create-admin', [
            '--name'     => 'Another User',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1)
            ->expectsOutput("❌ User with email '{$email}' already exists.");
    }

    /**
     * Test that command returns non-zero exit code on invalid email.
     */
    public function test_command_fails_with_invalid_email(): void
    {
        $this->artisan('migrate:fresh')->run();

        $this->artisan('user:create-admin', [
            '--name'     => 'Test User',
            '--email'    => 'not-an-email',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');
    }

    /**
     * Test that command fails with short password.
     */
    public function test_command_fails_with_short_password(): void
    {
        $this->artisan('migrate:fresh')->run();

        $this->artisan('user:create-admin', [
            '--name'     => 'Test User',
            '--email'    => 'user@example.com',
            '--password' => 'short',
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');
    }

    /**
     * Test that command fails with short name.
     */
    public function test_command_fails_with_short_name(): void
    {
        $this->artisan('migrate:fresh')->run();

        $this->artisan('user:create-admin', [
            '--name'     => 'A',
            '--email'    => 'user@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');
    }

    /**
     * Test that no partial records are created when validation fails.
     */
    public function test_no_partial_records_on_validation_failure(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $userCountBefore = User::count();

        // Attempt with invalid email
        $this->artisan('user:create-admin', [
            '--name'     => 'Test User',
            '--email'    => 'invalid-email',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1);

        // User should not be created
        $this->assertEquals($userCountBefore, User::count());
    }

    /**
     * Test that no partial records are created when email already exists.
     */
    public function test_no_partial_records_when_email_exists(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $email = 'existing@example.com';
        User::factory()->create(['email' => $email]);

        $userCountBefore = User::count();
        $managementDept  = Department::where('name', 'Management')->first();
        $ownerRole       = Role::where('name', 'owner')->first();

        $managementLinks = $managementDept ? $managementDept->users()->count() : 0;
        $ownerRoleLinks  = $ownerRole ? $ownerRole->users()->count() : 0;

        // Attempt to create user with existing email
        $this->artisan('user:create-admin', [
            '--name'     => 'Another User',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1);

        // Verify no new records created
        $this->assertEquals($userCountBefore, User::count());
        $this->assertEquals($managementLinks, $managementDept ? $managementDept->users()->count() : 0);
        $this->assertEquals($ownerRoleLinks, $ownerRole ? $ownerRole->users()->count() : 0);
    }

    // ──────────────────────────────────────────────
    // Task 3.4: Relationship attachments
    // ──────────────────────────────────────────────

    /**
     * Test that the user is attached to the Management department via the pivot table.
     */
    public function test_user_attached_to_management_department(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $email = 'admin@example.com';

        $this->artisan('user:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $user       = User::where('email', $email)->first();
        $management = Department::where('name', 'Management')->first();

        $this->assertNotNull($management);
        $this->assertTrue($user->department()->where('department_id', $management->id)->exists());
    }

    /**
     * Test that the user is assigned the owner role via the pivot table.
     */
    public function test_user_assigned_owner_role(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $email = 'admin@example.com';

        $this->artisan('user:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $user      = User::where('email', $email)->first();
        $ownerRole = Role::where('name', 'owner')->first();

        $this->assertNotNull($ownerRole);
        $this->assertTrue($user->roles()->where('role_id', $ownerRole->id)->exists());
    }

    /**
     * Test that relationships are accessible through the User model's relationship methods.
     */
    public function test_relationships_accessible_through_model(): void
    {
        $this->artisan('migrate:fresh --seed')->run();

        $email = 'admin@example.com';

        $this->artisan('user:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $user = User::where('email', $email)->first();

        // Check department relationship
        $departments = $user->department()->get();
        $this->assertCount(1, $departments);
        $this->assertEquals('Management', $departments->first()->name);

        // Check roles relationship
        $roles = $user->roles()->get();
        $this->assertCount(1, $roles);
        $this->assertEquals('owner', $roles->first()->name);
    }

    // ──────────────────────────────────────────────
    // Task 3.5: Interactive and non-interactive modes
    // ──────────────────────────────────────────────

    /**
     * Test non-interactive mode with all options provided directly.
     */
    public function test_non_interactive_mode_with_all_options(): void
    {
        $this->artisan('migrate:fresh')->run();

        $email    = 'admin@example.com';
        $name     = 'Admin User';
        $password = 'SecureP@ssw0rd';

        $output = $this->artisan('user:create-admin', [
            '--name'     => $name,
            '--email'    => $email,
            '--password' => $password,
        ])->run();

        $this->assertEquals(0, $output);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertEquals('en', $user->language);
    }

    /**
     * Test interactive mode with simulated user input.
     */
    public function test_interactive_mode_with_user_input(): void
    {
        $this->artisan('migrate:fresh')->run();

        $email    = 'interactive@example.com';
        $name     = 'Interactive User';
        $password = 'InteractiveP@ss123';

        $this->artisan('user:create-admin')
            ->expectsQuestion('What is the admin name?', $name)
            ->expectsQuestion('What is the admin email?', $email)
            ->expectsQuestion('What is the admin password?', $password)
            ->assertExitCode(0);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * Test mixed mode where some options are provided and some are prompted.
     */
    public function test_mixed_mode_with_partial_options(): void
    {
        $this->artisan('migrate:fresh')->run();

        $email    = 'mixed@example.com';
        $name     = 'Mixed User';
        $password = 'MixedP@ss123';

        $this->artisan('user:create-admin', [
            '--name'  => $name,
            '--email' => $email,
        ])
            ->expectsQuestion('What is the admin password?', $password)
            ->assertExitCode(0);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * Test that interactive and non-interactive modes produce identical user records.
     */
    public function test_both_modes_produce_identical_results(): void
    {
        // Non-interactive run
        $this->artisan('migrate:fresh')->run();

        $email1    = 'test1@example.com';
        $name1     = 'Test User One';
        $password1 = 'TestP@ssw0rd123';

        $this->artisan('user:create-admin', [
            '--name'     => $name1,
            '--email'    => $email1,
            '--password' => $password1,
        ])->assertExitCode(0);

        $user1 = User::where('email', $email1)->first();

        // Reset and do interactive run with same values
        $this->artisan('migrate:fresh')->run();

        $email2    = 'test2@example.com';
        $name2     = 'Test User Two';
        $password2 = 'TestP@ssw0rd123';

        $this->artisan('user:create-admin')
            ->expectsQuestion('What is the admin name?', $name2)
            ->expectsQuestion('What is the admin email?', $email2)
            ->expectsQuestion('What is the admin password?', $password2)
            ->assertExitCode(0);

        $user2 = User::where('email', $email2)->first();

        // Compare attributes (excluding email and name which intentionally differ)
        $this->assertEquals($user1->language, $user2->language);
        $this->assertNotNull($user1->external_id);
        $this->assertNotNull($user2->external_id);
        $this->assertTrue(Hash::check($password1, $user1->password));
        $this->assertTrue(Hash::check($password2, $user2->password));

        // Both should have owner role
        $ownerRole = Role::where('name', 'owner')->first();
        $this->assertTrue($user1->roles()->where('role_id', $ownerRole->id)->exists());
        $this->assertTrue($user2->roles()->where('role_id', $ownerRole->id)->exists());

        // Both should be in Management department
        $management = Department::where('name', 'Management')->first();
        $this->assertTrue($user1->department()->where('department_id', $management->id)->exists());
        $this->assertTrue($user2->department()->where('department_id', $management->id)->exists());
    }
}
