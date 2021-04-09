<?php
namespace Tests\Unit\DemoEnvironment;

use Tests\TestCase;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Department;
use App\Http\Middleware\RedirectIfDemo;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CanNotAccessTest extends TestCase
{
    use DatabaseTransactions;

    private $task;
    private $invoice;

    public function setUp(): void
    {
        parent::setUp();
        
        app()->detectEnvironment(function() { return 'demo'; });        
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function updateSettings()
    {
        $response = $this->json('PATCH', route('settings.update', []));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }

    /** @test */
    public function accessIntegrationsPage()
    {
        $response = $this->json('GET', route('integrations.index'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }


    /** @test */
    public function connectIntegrationsIntegration()
    {
        $response = $this->json('POST', route('integrations.store'));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }

    /** @test */
    public function deleteRole()
    {
        $role = factory(Role::class)->create();

        $response = $this->json('DELETE', route('roles.destroy', $role->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }

    // /** @test */
    // public function deleteTask()
    // {
    //     $task = factory(Task::class)->create();

    //     $response = $this->json('DELETE', route('tasks.destroy', $task->external_id));
    //     $this->assertEquals(302, $response->getStatusCode());
    //     $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    // }

    // /** @test */
    // public function deleteLead()
    // {
    //     $lead = factory(Lead::class)->create();

    //     $response = $this->json('DELETE', route('leads.destroy', $lead->external_id));
    //     $this->assertEquals(302, $response->getStatusCode());
    //     $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    // }
    
    /** @test */
    public function deleteClient()
    {
        $client = factory(Client::class)->create();

        $response = $this->json('DELETE', route('clients.destroy', $client->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }

    /** @test */
    public function deleteUser()
    {
        $user = factory(User::class)->create();

        $response = $this->json('DELETE', route('users.destroy', $user->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }


    /** @test */
    public function updateUser()
    {
        $user = factory(User::class)->create();

        $response = $this->json('PATCH', route('users.update', $user->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }

    /** @test */
    public function deleteDepartment()
    {
        $department = factory(Department::class)->create();

        $response = $this->json('DELETE', route('departments.destroy', $department->external_id));
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(RedirectIfDemo::MEESAGE, $response->getSession()->get("flash_message_warning"));
    }

}
