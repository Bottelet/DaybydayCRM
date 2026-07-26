<?php

namespace Tests\Feature\Departments;

use App\Http\Controllers\DepartmentsController;
use App\Models\Department;
use App\Models\User;
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
    public function it_rejects_department_creation_with_missing_required_name(): void
    {
        /* Arrange */
        /* Act */
        $response = $this->from(route('departments.create'))->post(route('departments.store'), [
            'description' => 'This is a test department',
            // name intentionally missing
        ]);

        /* Assert */
        $response->assertRedirect(route('departments.create'));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('departments', ['description' => 'This is a test department']);
    }

    #[Test]
    public function it_denies_department_creation_for_user_without_administrator_or_owner_role(): void
    {
        /* Arrange */
        $user = User::factory()->withRole('employee')->create();
        $this->actingAs($user);

        /* Act */
        $response = $this->from(route('departments.index'))->post(route('departments.store'), [
            'name'        => 'Unauthorized Department',
            'description' => 'Should not be created',
        ]);

        /* Assert: StoreDepartmentRequest::authorize() failing throws AuthorizationException,
         * which the app's exception Handler converts to a flash+redirect-back for
         * non-JSON requests instead of Laravel's generic 403 error page. */
        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('flash_message_warning', 'This action is unauthorized.');
        $this->assertDatabaseMissing('departments', ['name' => 'Unauthorized Department']);
    }

    #[Test]
    public function it_can_delete_department(): void
    {
        /* Arrange */
        $department = Department::factory()->create();
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());

        /* Act */
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
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());

        /* Act */
        $this->delete(route('departments.destroy', $department->external_id));

        /* Assert */
        $this->assertNotNull(Session::get('flash_message_warning'));
        $this->assertNotNull(Department::where('external_id', $department->external_id)->first());
    }
}
