<?php

namespace Tests\Feature\Users;

use App\Http\Controllers\UsersController;
use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

#[CoversClass(UsersController::class)]
class UsersControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('junie_repaired')]
    public function it_allows_owner_to_update_user_role()
    {
        /* Arrange */
        $this->asOwner();
        Cache::tags('role_user')->flush();
        $targetUser = User::factory()->withRole('employee')->create();
        /** @var Role $targetRole */
        $targetRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'description' => 'Manager role']);

        /* Act */
        $this->withoutMiddleware()->json(
            'PATCH',
            route('users.update', $targetUser->external_id),
            [
                'name'       => $targetUser->name,
                'email'      => $targetUser->email,
                'department' => $targetUser->department()->first()->id,
                'role'       => $targetRole->id,
            ]
        )->assertRedirect();

        /* Assert */
        $this->assertEquals(
            [$targetRole->id],
            $targetUser->roles()->get()->pluck('id')->toArray()
        );
    }

    #[Test]
    public function it_only_owner_role_can_update_user()
    {
        /* Arrange */
        /** @var User $manager */
        $manager    = User::factory()->withRole('manager')->create();
        $targetUser = User::factory()->create();
        $this->actingAs($manager);

        /* Act */
        $response = $this->withoutMiddleware()->json(
            'PATCH',
            route('users.update', $targetUser->external_id)
        );

        /* Assert */
        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_web_error_when_user_creation_throws_exception()
    {
        /* Arrange */
        $this->asOwner();
        $role       = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $department = Department::factory()->create();
        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'country'        => 'US',
                'company'        => 'Test Company',
                'max_users'      => User::query()->count() + 10,
                'vat'            => 0,
                'currency'       => 'USD',
                'language'       => 'en',
            ]
        );
        Storage::shouldReceive('put')->once()->andThrow(new RuntimeException('Simulated storage failure'));

        /* Act */
        $response = $this->from(route('users.create'))
            ->post(route('users.store'), $this->validUserPayload($role->id, $department->id));

        /* Assert */
        $response->assertRedirect(route('users.create'));
        $response->assertSessionHasErrors(['user']);
    }

    #[Test]
    public function it_returns_json_error_when_user_creation_throws_exception()
    {
        /* Arrange */
        $this->asOwner();
        $role       = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $department = Department::factory()->create();
        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'country'        => 'US',
                'company'        => 'Test Company',
                'max_users'      => User::query()->count() + 10,
                'vat'            => 0,
                'currency'       => 'USD',
                'language'       => 'en',
            ]
        );
        Storage::shouldReceive('put')->once()->andThrow(new RuntimeException('Simulated storage failure'));

        /* Act */
        $response = $this->withHeaders(['Accept' => 'application/json'])->post(route('users.store'), $this->validUserPayload($role->id, $department->id));

        /* Assert */
        $response->assertStatus(500);
        $response->assertJson([
            'message' => __('User could not be created. Please try again.'),
        ]);
    }

    private function validUserPayload(int $roleId, int $departmentId): array
    {
        return [
            'name'                  => 'Test User',
            'email'                 => 'user' . uniqid('', true) . '@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'roles'                 => $roleId,
            'departments'           => $departmentId,
            'language'              => 'en',
            'image_path'            => UploadedFile::fake()->image('avatar.jpg'),
        ];
    }
}
