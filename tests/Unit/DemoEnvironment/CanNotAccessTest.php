<?php

namespace Tests\Unit\DemoEnvironment;

use App\Http\Middleware\RedirectIfDemo;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Department;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CanNotAccessTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $task;

    private $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        app()->detectEnvironment(function () {
            return 'demo';
        });
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function update_settings()
    {
        $response = $this->json('PATCH', route('settings.updateOverall', []));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    #[Test]
    public function access_integrations_page()
    {
        $response = $this->json('GET', route('integrations.index'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    #[Test]
    public function connect_integrations_integration()
    {
        $response = $this->json('POST', route('integrations.store'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    #[Test]
    public function delete_role()
    {
        $role = Role::factory()->create();

        $response = $this->json('DELETE', route('roles.destroy', $role->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    // #[Test]
    // public function deleteTask()
    // {
    //     $task = Task::factory()->create();

    //     $response = $this->json('DELETE', route('tasks.destroy', $task->external_id));
    //     $this->assertEquals(302, $response->getStatusCode());
    //     $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    // }

    // #[Test]
    // public function deleteLead()
    // {
    //     $lead = Lead::factory()->create();

    //     $response = $this->json('DELETE', route('leads.destroy', $lead->external_id));
    //     $this->assertEquals(302, $response->getStatusCode());
    //     $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    // }

    #[Test]
    public function delete_client()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::factory()->create();

        $response = $this->json('DELETE', route('clients.destroy', $client->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    #[Test]
    public function delete_user()
    {
        $authUser = User::factory()->create();
        $this->actingAs($authUser);

        $user = User::factory()->create();

        $response = $this->json('DELETE', route('users.destroy', $user->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    #[Test]
    public function update_user()
    {
        $authUser = User::factory()->create();
        $this->actingAs($authUser);

        $user = User::factory()->create();

        $response = $this->json('PATCH', route('users.update', $user->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }

    #[Test]
    public function delete_department()
    {
        $department = Department::factory()->create();

        $response = $this->json('DELETE', route('departments.destroy', $department->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get('flash_message_warning'));
    }
}
