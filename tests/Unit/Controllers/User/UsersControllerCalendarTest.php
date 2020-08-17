<?php
namespace Tests\Unit\Controllers\User;

use App\Models\Absence;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersControllerCalendarTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected $absenceWithInTime;
    protected $absenceWithToLate;
    protected $absenceWithToEarly;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->absenceWithInTime = factory(Absence::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now(),
            'end_at' => now()->addDay(),
            'reason' => 'test'
        ]);

        $this->absenceWithToLate = factory(Absence::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now()->addWeeks(5),
            'end_at' => now()->addWeeks(6),
            'reason' => 'test'
        ]);
        $this->absenceWithToEarly = factory(Absence::class)->create([
            'user_id' => $this->user->id,
            'start_at' => now()->subWeeks(4),
            'end_at' => now()->subWeeks(3),
            'reason' => 'test'
        ]);
    }

    /** @test * */
    public function can_get_absences_within_time_slot()
    {
        $correctUser = null;
        $r = $this->json('GET', '/users/calendar-users/');
        foreach ($r->decodeResponseJson() as $user) {
            if ($user["external_id"] == $this->user->external_id) {
                $correctUser = $user;
            }
        }

        $this->assertCount(1, $correctUser["absences"]);
        $this->assertEquals($this->absenceWithInTime->start_at, $correctUser["absences"][0]["start_at"]);
        $this->assertEquals($this->absenceWithInTime->end_at, $correctUser["absences"][0]["end_at"]);

        $this->assertCount(3, User::whereExternalId($correctUser["external_id"])->first()->absences);
    }
}
