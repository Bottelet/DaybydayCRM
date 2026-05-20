<?php

namespace Tests\Feature\Commands;

use App\Enums\RoleType;
use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class CreateAdminUserCommandTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Consistent DB strategy: start each test with a completely empty database.
        // RefreshDatabase is not used; migrations are managed once per test here.
        $this->artisan('migrate:fresh')->run();
    }

    // ──────────────────────────────────────────────
    // Task 3.1: Fresh database scenarios
    // ──────────────────────────────────────────────

    #[Test]
    public function it_creates_admin_user_on_fresh_database(): void
    {
        /* Arrange */
        $email = 'admin@example.com';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Admin User',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('Admin User', $user->name);
        $this->assertTrue(Hash::check('SecureP@ssw0rd', $user->password));
    }

    #[Test]
    public function it_auto_provisions_dependencies_on_fresh_database(): void
    {
        /* Arrange */
        $this->assertCount(0, Setting::all());
        $this->assertCount(0, Role::all());
        $this->assertCount(0, Department::all());

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => 'admin@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $this->assertCount(1, Setting::all());
        $this->assertTrue(Role::where('name', RoleType::OWNER->value)->exists());
        $this->assertTrue(Department::where('name', Department::MANAGEMENT)->exists());
    }

    #[Test]
    public function it_creates_user_with_correct_attributes(): void
    {
        /* Arrange */
        $email    = 'admin@example.com';
        $name     = 'John Doe';
        $password = 'TestP@ss123';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => $name,
            '--email'    => $email,
            '--password' => $password,
        ])->assertExitCode(0);

        /* Assert */
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotNull($user->external_id);
        $this->assertEquals('en', $user->language);
    }

    // ──────────────────────────────────────────────
    // Task 3.2: Seeded database compatibility
    // ──────────────────────────────────────────────

    #[Test]
    public function it_creates_admin_on_seeded_database(): void
    {
        /* Arrange */
        $this->artisan('migrate:fresh --seed')->run();
        $email = 'newadmin@example.com';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'New Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
    }

    #[Test]
    public function it_reuses_existing_dependencies(): void
    {
        /* Arrange */
        $this->artisan('migrate:fresh --seed')->run();
        $settingsCountBefore   = Setting::count();
        $roleCountBefore       = Role::count();
        $departmentCountBefore = Department::count();

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'First Admin',
            '--email'    => 'first@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Second Admin',
            '--email'    => 'second@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $this->assertEquals($settingsCountBefore, Setting::count());
        $this->assertEquals($roleCountBefore, Role::count());
        $this->assertEquals($departmentCountBefore, Department::count());
    }

    #[Test]
    public function it_creates_no_duplicate_records_on_multiple_runs(): void
    {
        /* Arrange */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Admin One',
            '--email'    => 'admin1@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        $settingsCount   = Setting::count();
        $roleCount       = Role::count();
        $departmentCount = Department::count();

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Admin Two',
            '--email'    => 'admin2@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $this->assertEquals($settingsCount, Setting::count());
        $this->assertEquals($roleCount, Role::count());
        $this->assertEquals($departmentCount, Department::count());
        $this->assertEquals(2, User::count());
    }

    // ──────────────────────────────────────────────
    // Task 3.3: Error handling
    // ──────────────────────────────────────────────

    #[Test]
    public function it_fails_with_duplicate_email(): void
    {
        /* Arrange */
        $email = 'duplicate@example.com';
        User::factory()->create(['email' => $email]);

        /* Act & Assert */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Another User',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1)
            ->expectsOutput("❌ User with email '{$email}' already exists.");
    }

    #[Test]
    public function it_fails_with_invalid_email(): void
    {
        /* Arrange – no extra setup needed; fresh DB from setUp */

        /* Act & Assert */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test User',
            '--email'    => 'not-an-email',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');
    }

    #[Test]
    public function it_fails_with_short_password(): void
    {
        /* Arrange – no extra setup needed; fresh DB from setUp */

        /* Act & Assert */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test User',
            '--email'    => 'user@example.com',
            '--password' => 'short',
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');
    }

    #[Test]
    public function it_fails_with_short_name(): void
    {
        /* Arrange – no extra setup needed; fresh DB from setUp */

        /* Act & Assert */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'A',
            '--email'    => 'user@example.com',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');
    }

    #[Test]
    public function it_creates_no_partial_records_on_validation_failure(): void
    {
        /* Arrange */
        $userCountBefore = User::count();

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test User',
            '--email'    => 'invalid-email',
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1);

        /* Assert */
        $this->assertEquals($userCountBefore, User::count());
    }

    #[Test]
    public function it_creates_no_partial_records_when_email_exists(): void
    {
        /* Arrange */
        $this->artisan('migrate:fresh --seed')->run();
        $email          = 'existing@example.com';
        User::factory()->create(['email' => $email]);

        $userCountBefore = User::count();
        $managementDept  = Department::where('name', Department::MANAGEMENT)->first();
        $ownerRole       = Role::where('name', RoleType::OWNER->value)->first();
        $managementLinks = $managementDept ? $managementDept->users()->count() : 0;
        $ownerRoleLinks  = $ownerRole ? $ownerRole->users()->count() : 0;

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Another User',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(1);

        /* Assert */
        $this->assertEquals($userCountBefore, User::count());
        $this->assertEquals($managementLinks, $managementDept ? $managementDept->users()->count() : 0);
        $this->assertEquals($ownerRoleLinks, $ownerRole ? $ownerRole->users()->count() : 0);
    }

    // ──────────────────────────────────────────────
    // Task 3.4: Relationship attachments
    // ──────────────────────────────────────────────

    #[Test]
    public function it_attaches_user_to_management_department(): void
    {
        /* Arrange */
        $email = 'admin@example.com';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $user       = User::where('email', $email)->first();
        $management = Department::where('name', Department::MANAGEMENT)->first();
        $this->assertNotNull($management);
        $this->assertTrue($user->department()->where('department_id', $management->id)->exists());
    }

    #[Test]
    public function it_assigns_owner_role_to_user(): void
    {
        /* Arrange */
        $email = 'admin@example.com';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $user      = User::where('email', $email)->first();
        $ownerRole = Role::where('name', RoleType::OWNER->value)->first();
        $this->assertNotNull($ownerRole);
        $this->assertTrue($user->roles()->where('role_id', $ownerRole->id)->exists());
    }

    #[Test]
    public function it_makes_relationships_accessible_through_model(): void
    {
        /* Arrange */
        $email = 'admin@example.com';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => 'Test Admin',
            '--email'    => $email,
            '--password' => 'SecureP@ssw0rd',
        ])->assertExitCode(0);

        /* Assert */
        $user        = User::where('email', $email)->first();
        $departments = $user->department()->get();
        $this->assertCount(1, $departments);
        $this->assertEquals(Department::MANAGEMENT, $departments->first()->name);

        $roles = $user->roles()->get();
        $this->assertCount(1, $roles);
        $this->assertEquals(RoleType::OWNER->value, $roles->first()->name);
    }

    // ──────────────────────────────────────────────
    // Task 3.5: Interactive and non-interactive modes
    // ──────────────────────────────────────────────

    #[Test]
    public function it_runs_successfully_in_non_interactive_mode(): void
    {
        /* Arrange */
        $email    = 'admin@example.com';
        $name     = 'Admin User';
        $password = 'SecureP@ssw0rd';

        /* Act */
        $exitCode = $this->artisan('daybyday:create-admin', [
            '--name'     => $name,
            '--email'    => $email,
            '--password' => $password,
        ])->run();

        /* Assert */
        $this->assertEquals(0, $exitCode);
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertEquals('en', $user->language);
    }

    #[Test]
    public function it_runs_successfully_in_interactive_mode(): void
    {
        /* Arrange */
        $email    = 'interactive@example.com';
        $name     = 'Interactive User';
        $password = 'InteractiveP@ss123';

        /* Act & Assert */
        $this->artisan('daybyday:create-admin')
            ->expectsQuestion('What is the admin name?', $name)
            ->expectsQuestion('What is the admin email?', $email)
            ->expectsQuestion('What is the admin password?', $password)
            ->assertExitCode(0);

        /* Assert */
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    #[Test]
    public function it_runs_in_mixed_mode_with_partial_options(): void
    {
        /* Arrange */
        $email    = 'mixed@example.com';
        $name     = 'Mixed User';
        $password = 'MixedP@ss123';

        /* Act & Assert */
        $this->artisan('daybyday:create-admin', [
            '--name'  => $name,
            '--email' => $email,
        ])
            ->expectsQuestion('What is the admin password?', $password)
            ->assertExitCode(0);

        /* Assert */
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    #[Test]
    public function it_fails_with_validation_error_in_no_interaction_mode_without_required_options(): void
    {
        /* Act & Assert */
        $this->artisan('daybyday:create-admin', [
            '--no-interaction' => true,
        ])->assertExitCode(1)
            ->expectsOutput('❌ Validation failed:');

        $this->assertCount(0, User::all());
    }

    #[Test]
    public function it_produces_identical_results_in_both_modes(): void
    {
        /* Arrange */
        $email1    = 'test1@example.com';
        $name1     = 'Test User One';
        $password1 = 'TestP@ssw0rd123';

        $email2    = 'test2@example.com';
        $name2     = 'Test User Two';
        $password2 = 'TestP@ssw0rd123';

        /* Act */
        $this->artisan('daybyday:create-admin', [
            '--name'     => $name1,
            '--email'    => $email1,
            '--password' => $password1,
        ])->assertExitCode(0);

        $this->artisan('daybyday:create-admin')
            ->expectsQuestion('What is the admin name?', $name2)
            ->expectsQuestion('What is the admin email?', $email2)
            ->expectsQuestion('What is the admin password?', $password2)
            ->assertExitCode(0);

        /* Assert */
        $user1 = User::where('email', $email1)->first();
        $user2 = User::where('email', $email2)->first();

        $this->assertNotNull($user1);
        $this->assertNotNull($user2);
        $this->assertEquals($user1->language, $user2->language);
        $this->assertNotNull($user1->external_id);
        $this->assertNotNull($user2->external_id);
        $this->assertTrue(Hash::check($password1, $user1->password));
        $this->assertTrue(Hash::check($password2, $user2->password));

        $ownerRole = Role::where('name', RoleType::OWNER->value)->first();
        $this->assertNotNull($ownerRole);
        $this->assertTrue($user1->roles()->where('role_id', $ownerRole->id)->exists());
        $this->assertTrue($user2->roles()->where('role_id', $ownerRole->id)->exists());

        $management = Department::where('name', Department::MANAGEMENT)->first();
        $this->assertNotNull($management);
        $this->assertTrue($user1->department()->where('department_id', $management->id)->exists());
        $this->assertTrue($user2->department()->where('department_id', $management->id)->exists());
    }
}
