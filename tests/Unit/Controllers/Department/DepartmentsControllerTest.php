<?php

namespace Tests\Unit\Controllers\Department;

use App\Models\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepartmentsControllerTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function can_create_department()
    {
        $response = $this->json('POST', route('departments.store'), [
            'name' => 'Test Department',
            'description' => 'This is a test department',
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotNull(Department::where('name', 'Test Department')->first());
    }

    #[Test]
    public function can_delete_department()
    {
        $department = Department::factory()->create();

        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
        $this->json('DELETE', route('departments.destroy', $department->external_id));
        $this->assertNull(Department::where('external_id', $department->external_id)->first());
    }

    #[Test]
    public function cant_delete_department_if_user_is_associated()
    {
        $department = Department::factory()->create();
        $this->user->department()->attach([$department->id]);

        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());

        $this->json('DELETE', route('departments.destroy', $department->external_id));
        $this->assertNotNull(\Session::all()['flash_message_warning']);
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
    }
}
