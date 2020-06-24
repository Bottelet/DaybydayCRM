<?php
namespace Tests\Unit\Controllers\Department;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;
use App\Models\Department;
use Ramsey\Uuid\Uuid;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DepartmentsControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /** @test **/
    public function can_create_department()
    {
        $response = $this->json('POST', route('departments.store'), [
                'name' => 'Test Department',
                'description' => 'This is a test department',
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotNull(Department::where('name', 'Test Department')->first());
    }

    /** @test **/
    public function can_delete_department()
    {
        $department = factory(Department::class)->create();

        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
        $this->json('DELETE', route('departments.destroy', $department->external_id));
        $this->assertNull(Department::where('external_id', $department->external_id)->first());
    }

    /** @test **/
    public function cant_delete_department_if_user_is_associated()
    {
        $department = factory(Department::class)->create();
        $this->user->department()->attach([$department->id]);

        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());

        $this->json('DELETE', route('departments.destroy', $department->external_id));
        $this->assertNotNull(\Session::all()["flash_message_warning"]);
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
    }
}
