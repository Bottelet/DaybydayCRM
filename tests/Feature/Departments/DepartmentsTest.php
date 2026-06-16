<?php

namespace Tests\Feature\Departments;

use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DepartmentsController;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(DepartmentsController::class)]
class DepartmentsTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_can_create_department(): void
    {
        /* Arrange */
        /* Act */
        $response = $this->post(route('departments.store'), [
            'name'        => 'Test Department',
            'description' => 'This is a test department',
        ]);

        /* Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotNull(Department::where('name', 'Test Department')->first());
    }

    #[Test]
    public function it_can_delete_department(): void
    {
        /* Arrange */
        $department = Department::factory()->create();

        /* Act */
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
        $this->delete(route('departments.destroy', $department->external_id));

        /* Assert */
        $this->assertNull(Department::where('external_id', $department->external_id)->first());
    }

    #[Test]
    public function it_cannot_delete_department_if_user_is_associated(): void
    {
        /* Arrange */
        $department = Department::factory()->create();
        $this->user->department()->attach([$department->id]);

        /* Act */
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
        $this->delete(route('departments.destroy', $department->external_id));

        /* Assert */
        $this->assertNotNull(Session::get('flash_message_warning'));
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
    }
}
