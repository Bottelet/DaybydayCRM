<?php

namespace Tests\Unit\Absences;

use App\Models\Absence;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Absence\AbsenceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class AbsenceServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected AbsenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AbsenceService::class);
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_store_absence_creates_absence_for_current_user_when_no_user_specified()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'vacation',
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/25',
            'radio'      => 'irrelevant',
            'comment'    => 'Vacation time',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $this->assertDatabaseHas('absences', [
            'user_id' => $user->id,
            'reason'  => 'vacation',
        ]);
    }

    #[Test]
    public function it_store_absence_creates_absence_for_specified_user_when_user_has_manage_permission()
    {
        /* Arrange */
        $manager    = User::factory()->create();
        $targetUser = User::factory()->create();
        $this->actingAs($manager);

        // Create a role with the absence-manage permission
        $managerRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager']);
        $permission  = Permission::firstOrCreate(['name' => 'absence-manage']);
        $managerRole->attachPermission($permission);
        $manager->attachRole($managerRole);
        $manager = $manager->fresh();

        $request = $this->createMockRequest([
            'user_external_id' => $targetUser->external_id,
            'reason'           => 'vacation',
            'start_date'       => '2024/01/20',
            'end_date'         => '2024/01/25',
            'radio'            => 'irrelevant',
            'comment'          => 'Vacation time',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $this->assertDatabaseHas('absences', [
            'user_id' => $targetUser->id,
            'reason'  => 'vacation',
        ]);
    }

    #[Test]
    public function it_store_absence_returns_error_when_creating_for_other_user_without_permission()
    {
        /* Arrange */
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'user_external_id' => $otherUser->external_id,
            'reason'           => 'vacation',
            'start_date'       => '2024/01/20',
            'end_date'         => '2024/01/25',
            'radio'            => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('permission', $result['error']);
    }

    #[Test]
    public function it_store_absence_returns_error_when_reason_is_missing()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/25',
            'radio'      => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Reason', $result['error']);
    }

    #[Test]
    public function it_store_absence_returns_error_when_start_date_is_missing()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'   => 'vacation',
            'end_date' => '2024/01/25',
            'radio'    => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Start date', $result['error']);
    }

    #[Test]
    public function it_store_absence_returns_error_when_end_date_is_missing()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'vacation',
            'start_date' => '2024/01/20',
            'radio'      => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('End date', $result['error']);
    }

    #[Test]
    public function it_store_absence_returns_error_when_user_not_found()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'user_external_id' => 'nonexistent-id',
            'reason'           => 'vacation',
            'start_date'       => '2024/01/20',
            'end_date'         => '2024/01/25',
            'radio'            => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('not found', $result['error']);
    }

    #[Test]
    public function it_store_absence_returns_error_when_start_date_is_after_end_date()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'vacation',
            'start_date' => '2024/01/25',
            'end_date'   => '2024/01/20',
            'radio'      => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('End date must be after', $result['error']);
    }

    #[Test]
    public function it_store_absence_accepts_equal_start_and_end_dates()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'vacation',
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/20',
            'radio'      => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $this->assertDatabaseHas('absences', [
            'user_id' => $user->id,
            'reason'  => 'vacation',
        ]);
    }

    #[Test]
    public function it_store_absence_handles_medical_certificate_true()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'sick_leave',
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/25',
            'radio'      => 'true',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $this->assertDatabaseHas('absences', [
            'user_id'             => $user->id,
            'medical_certificate' => true,
        ]);
    }

    #[Test]
    public function it_store_absence_handles_medical_certificate_false()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'sick_leave',
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/25',
            'radio'      => 'false',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $this->assertDatabaseHas('absences', [
            'user_id'             => $user->id,
            'medical_certificate' => false,
        ]);
    }

    #[Test]
    public function it_store_absence_handles_medical_certificate_irrelevant()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = $this->createMockRequest([
            'reason'     => 'vacation',
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/25',
            'radio'      => 'irrelevant',
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $this->assertDatabaseHas('absences', [
            'user_id'             => $user->id,
            'medical_certificate' => null,
        ]);
    }

    #[Test]
    public function it_store_absence_stores_comment()
    {
        /* Arrange */
        $user = User::factory()->create();
        $this->actingAs($user);

        $comment = 'This is a test comment';
        $request = $this->createMockRequest([
            'reason'     => 'vacation',
            'start_date' => '2024/01/20',
            'end_date'   => '2024/01/25',
            'radio'      => 'irrelevant',
            'comment'    => $comment,
        ]);

        /* Act */
        $result = $this->service->storeAbsence($request);

        /* Assert */
        $this->assertEmpty($result);
        $absence = \App\Models\Absence::where('user_id', $user->id)->first();
        $this->assertNotNull($absence);
        $this->assertStringContainsString($comment, strip_tags($absence->comment));
    }

    #[Test]
    public function it_delete_absence_deletes_absence_record()
    {
        /* Arrange */
        $absence   = Absence::factory()->create();
        $absenceId = $absence->id;

        /* Act */
        $result = $this->service->deleteAbsence($absence);

        /* Assert */
        $this->assertTrue($result);
        $this->assertDatabaseMissing('absences', ['id' => $absenceId]);
    }

    #[Test]
    public function it_get_absences_for_user_returns_all_absences_for_user()
    {
        /* Arrange */
        $user = User::factory()->create();
        Absence::factory()->count(3)->create(['user_id' => $user->id]);
        Absence::factory()->count(2)->create();

        /* Act */
        $absences = $this->service->getAbsencesForUser($user);

        /* Assert */
        $this->assertCount(3, $absences);
        foreach ($absences as $absence) {
            $this->assertEquals($user->id, $absence->user_id);
        }
    }

    #[Test]
    public function it_get_absences_for_user_filters_by_date_range()
    {
        /* Arrange */
        $user = User::factory()->create();
        Absence::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-10'),
            'end_at'   => Carbon::parse('2024-01-15'),
        ]);
        Absence::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-02-01'),
            'end_at'   => Carbon::parse('2024-02-05'),
        ]);

        /* Act */
        $absences = $this->service->getAbsencesForUser(
            $user,
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-31')
        );

        /* Assert */
        $this->assertCount(1, $absences);
    }

    #[Test]
    public function it_user_has_absence_during_returns_true_when_absence_exists()
    {
        /* Arrange */
        $user = User::factory()->create();
        Absence::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-15'),
            'end_at'   => Carbon::parse('2024-01-20'),
        ]);

        /* Act */
        $result = $this->service->userHasAbsenceDuring(
            $user,
            Carbon::parse('2024-01-10'),
            Carbon::parse('2024-01-25')
        );

        /* Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function it_user_has_absence_during_returns_false_when_no_absence_exists()
    {
        /* Arrange */
        $user = User::factory()->create();
        Absence::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-01'),
            'end_at'   => Carbon::parse('2024-01-05'),
        ]);

        /* Act */
        $result = $this->service->userHasAbsenceDuring(
            $user,
            Carbon::parse('2024-01-15'),
            Carbon::parse('2024-01-25')
        );

        /* Assert */
        $this->assertFalse($result);
    }

    #[Test]
    public function it_user_has_absence_during_returns_true_when_absence_partially_overlaps()
    {
        /* Arrange */
        $user = User::factory()->create();
        Absence::factory()->create([
            'user_id'  => $user->id,
            'start_at' => Carbon::parse('2024-01-15'),
            'end_at'   => Carbon::parse('2024-01-20'),
        ]);

        /* Act */
        $result = $this->service->userHasAbsenceDuring(
            $user,
            Carbon::parse('2024-01-18'),
            Carbon::parse('2024-01-25')
        );

        /* Assert */
        $this->assertTrue($result);
    }

    /**
     * Helper method to create a mock request with the given data.
     *
     * @param array $data The request data
     *
     * @return \Illuminate\Http\Request The mock request
     */
    private function createMockRequest(array $data)
    {
        $request = new \Illuminate\Http\Request();
        $request->initialize([], $data, [], [], [], [
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
        ]);

        return $request;
    }
}
