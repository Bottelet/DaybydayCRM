<?php

namespace Tests\Unit\DemoEnvironment;

use App\Http\Middleware\RedirectIfDemo;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CanNotAccessTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->detectEnvironment(function () {
            return 'demo';
        });
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    // region happy_path

    #[Test]
    public function it_updates_settings()
    {
        /** Arrange */
        // Demo environment configured in setUp()

        /** Act */
        $response = $this->json('PATCH', route('settings.updateOverall', []));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_access_integrations_page()
    {
        /** Arrange */
        // Demo environment configured in setUp()

        /** Act */
        $response = $this->json('GET', route('integrations.index'));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_connect_integrations_integration()
    {
        /** Arrange */
        // Demo environment configured in setUp()

        /** Act */
        $response = $this->json('POST', route('integrations.store'));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_deletes_role()
    {
        /** Arrange */
        $role = Role::factory()->create();

        /** Act */
        $response = $this->json('DELETE', route('roles.destroy', $role->external_id));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_deletes_client()
    {
        /** Arrange */
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $user->attachRole($role);
        $permission = \App\Models\Permission::firstOrCreate(['name' => 'client-delete']);
        $role->attachPermission($permission);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->actingAs($user);
        $client = Client::factory()->create();

        /** Act */
        $response = $this->json('DELETE', route('clients.destroy', $client->external_id));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_deletes_user()
    {
        /** Arrange */
        $authUser = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $authUser->attachRole($role);
        $permission = \App\Models\Permission::firstOrCreate(['name' => 'user-delete']);
        $role->attachPermission($permission);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->actingAs($authUser);
        $user = User::factory()->create();

        /** Act */
        $response = $this->json('DELETE', route('users.destroy', $user->external_id));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_updates_user()
    {
        /** Arrange */
        $authUser = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
        $authUser->attachRole($role);
        $permission = \App\Models\Permission::firstOrCreate(['name' => 'user-update']);
        $role->attachPermission($permission);
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        $this->actingAs($authUser);
        $user = User::factory()->create();

        /** Act */
        $response = $this->json('PATCH', route('users.update', $user->external_id));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    #[Test]
    public function it_deletes_department()
    {
        /** Arrange */
        $department = Department::factory()->create();

        /** Act */
        $response = $this->json('DELETE', route('departments.destroy', $department->external_id));

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getsession()->get('flash_message_warning'));
    }

    // endregion
}
