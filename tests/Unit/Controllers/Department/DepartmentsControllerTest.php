<?php

namespace Tests\Unit\Controllers\Department;

use App\Models\Department;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class DepartmentsControllerTest extends AbstractTestCase
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

    # region crud

    #[Test]
    public function it_can_create_department()
    {
        /** Arrange */
        // Already arranged in setUp

        /** Act */
        $response = $this->json('POST', route('departments.store'), [
            'name' => 'Test Department',
            'description' => 'This is a test department',
        ]);

        /** Assert */
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNotNull(Department::where('name', 'Test Department')->first());
    }

    #[Test]
    public function it_can_delete_department()
    {
        /** Arrange */
        $department = Department::factory()->create();

        /** Act */
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
        $this->json('DELETE', route('departments.destroy', $department->external_id));

        /** Assert */
        $this->assertNull(Department::where('external_id', $department->external_id)->first());
    }

    # endregion

    # region failure_path

    #[Test]
    public function it_cant_delete_department_if_user_is_associated()
    {
        /** Arrange */
        $department = Department::factory()->create();
        $this->user->department()->attach([$department->id]);

        /** Act */
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
        $this->json('DELETE', route('departments.destroy', $department->external_id));

        /** Assert */
        $this->assertNotNull(Session::all()['flash_message_warning']);
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
    }

    # endregion
}
