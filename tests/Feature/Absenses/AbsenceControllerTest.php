<?php

namespace Tests\Feature\Absenses;

use App\Enums\PermissionName;
use App\Http\Controllers\AbsenceController;
use App\Models\User;
use App\Services\AbsenceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

#[CoversClass(AbsenceController::class)]
class AbsenceControllerTest extends AbstractTestCase
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
    #[Group('junie_repaired')]
    public function it_can_create_absence_for_other_user()
    {
        /* Arrange */
        $this->user = User::factory()->withRole('employee')->create();
        $this->withPermissions(PermissionName::ABSENCE_MANAGE);

        $this->assertTrue($this->user->can(PermissionName::ABSENCE_MANAGE->value), 'User should have absence-manage permission');

        $user = User::factory()->create();

        /* Act */
        $response = $this->json('POST', route('absence.store'), [
            'reason'              => 'Sick',
            'user_external_id'    => $user->external_id,
            'start_date'          => '2020-01-01',
            'end_date'            => '2020-01-02',
            'medical_certificate' => null,
            'comment'             => 'Sick kid',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $absences = $user->fresh()->absences;
        $this->assertCount(1, $absences);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_creates_absence_for_authenticated_user_when_user_external_id_not_provided()
    {
        /* Arrange */

        /* Act */
        $response = $this->json('POST', route('absence.store'), [
            'reason'              => 'Sick',
            'start_date'          => '2020-01-01',
            'end_date'            => '2020-01-02',
            'medical_certificate' => null,
            'comment'             => 'Sick kid',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertCount(1, $this->user->fresh()->absences);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_creates_absence_for_authenticated_user_when_attempting_to_create_for_other_user_without_permission()
    {
        /* Arrange */
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $absentUser = User::factory()->create();

        /* Act */
        $response = $this->json('POST', route('absence.store'), [
            'reason'              => 'Sick',
            'user_external_id'    => $absentUser->external_id,
            'start_date'          => '2020-01-01',
            'end_date'            => '2020-01-02',
            'medical_certificate' => null,
            'comment'             => 'Sick kid',
        ]);

        /* Assert */
        $response->assertStatus(200);
        $this->assertCount(0, $absentUser->fresh()->absences);
        $this->assertCount(1, $this->user->fresh()->absences);
    }

    #[Test]
    public function it_returns_web_error_when_absence_creation_throws_exception()
    {
        /* Arrange */
        $this->bindFailingAbsenceService();

        /* Act */
        $response = $this->from(route('absence.create'))->post(route('absence.store'), [
            'reason'     => 'Sick',
            'start_date' => '2020-01-01',
            'end_date'   => '2020-01-02',
        ]);

        /* Assert */
        $response->assertRedirect(route('absence.create'));
        $response->assertSessionHasErrors(['absence']);
    }

    #[Test]
    public function it_returns_json_error_when_absence_creation_throws_exception()
    {
        /* Arrange */
        $this->bindFailingAbsenceService();

        /* Act */
        $response = $this->json('POST', route('absence.store'), [
            'reason'     => 'Sick',
            'start_date' => '2020-01-01',
            'end_date'   => '2020-01-02',
        ]);

        /* Assert */
        $response->assertStatus(500);
        $response->assertJson([
            'message' => __('Absence could not be registered. Please try again.'),
        ]);
    }

    private function bindFailingAbsenceService(): void
    {
        $absenceService = Mockery::mock(AbsenceService::class);
        $absenceService->shouldReceive('storeAbsence')
            ->andThrow(new RuntimeException('Simulated absence create failure'));

        $this->app->instance(AbsenceService::class, $absenceService);
    }
}
