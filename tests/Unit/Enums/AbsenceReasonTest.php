<?php

namespace Tests\Unit\Enums;

use App\Enums\AbsenceReason;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class AbsenceReasonTest extends AbstractTestCase
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
    public function it_gets_display_value_from_reason()
    {
        /* Arrange */
        $statusValue = 'vacation';

        /* Act */
        $displayValue = AbsenceReason::fromStatus($statusValue)->getDisplayValue();

        /* Assert */
        $this->assertEquals('Vacation', $displayValue);
    }

    #[Test]
    public function it_gets_reason_from_display_value()
    {
        /* Arrange */
        $displayValue = 'Vacation';

        /* Act */
        $reason = AbsenceReason::fromDisplayValue($displayValue)->getReason();

        /* Assert */
        $this->assertEquals('vacation', $reason);
    }

    #[Test]
    public function it_absence_reason_contains_both_reason_and_display_value()
    {
        /* Arrange */
        $statusValue = 'vacation';

        /* Act */
        $reason = AbsenceReason::fromStatus($statusValue);

        /* Assert */
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    #[Test]
    public function it_accepts_a_null_display_value_in_the_constructor()
    {
        /* Arrange */
        $reasonValue  = 'custom_reason';
        $displayValue = null;

        /* Act */
        $reason = new AbsenceReason($reasonValue, $displayValue);

        /* Assert */
        $this->assertEquals('custom_reason', $reason->getReason());
        // getDisplayValue() has a non-nullable `string` return type, so a null
        // constructor argument must fall back to the reason string rather
        // than being returned as-is (which would be a TypeError).
        $this->assertEquals('custom_reason', $reason->getDisplayValue());
    }

    #[Test]
    public function it_throws_exception_if_display_value_is_not_known()
    {
        /* Arrange */
        $invalidDisplayValue = 'None existing display value';
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        AbsenceReason::fromDisplayValue($invalidDisplayValue);

        /* Assert */
    }

    #[Test]
    public function it_treats_display_value_lookup_as_case_sensitive()
    {
        /* Arrange */
        $wrongCase = 'vacation';
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        AbsenceReason::fromDisplayValue($wrongCase);

        /* Assert */
    }

    #[Test]
    public function it_throws_for_a_partial_display_value_match()
    {
        /* Arrange */
        $partialMatch = 'Vacatio';
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        AbsenceReason::fromDisplayValue($partialMatch);

        /* Assert */
    }

    #[Test]
    public function it_returns_an_absence_reason_instance_when_getting_reason()
    {
        /* Arrange */
        $statusValue = 'vacation';

        /* Act */
        $result = AbsenceReason::fromStatus($statusValue);

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $result);
    }

    #[Test]
    public function it_returns_the_correct_reason_value()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::sickLeave()->getReason();

        /* Assert */
        $this->assertEquals('sick_leave', $reason);
    }

    #[Test]
    public function it_values_contains_expected_absence_reason_keys()
    {
        /* Arrange */

        /* Act */
        $values = AbsenceReason::values();

        /* Assert */
        $this->assertArrayHasKey('sick_leave', $values);
        $this->assertArrayHasKey('personal_leave', $values);
        $this->assertArrayHasKey('vacation', $values);
        $this->assertArrayHasKey('vacation_day', $values);
        $this->assertArrayHasKey('time_off', $values);
        $this->assertArrayHasKey('time_off_in_lieu', $values);
        $this->assertArrayHasKey('flextime', $values);
        $this->assertArrayHasKey('other', $values);
    }

    #[Test]
    public function it_values_stores_distinct_reason_per_entry()
    {
        /* Arrange */

        /* Act */
        $values = AbsenceReason::values();

        /* Assert: guards against the values() array constructing an entry with
         * the wrong reason constant, which previously made 'time_off_in_lieu'
         * indistinguishable from 'time_off'. */
        $this->assertEquals('time_off', $values['time_off']->getReason());
        $this->assertEquals('time_off_in_lieu', $values['time_off_in_lieu']->getReason());
    }

    #[Test]
    public function it_returns_correct_reason_from_vacation_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::vacation();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    #[Test]
    public function it_returns_correct_reason_from_vacation_day_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::vacationDay();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation_day', $reason->getReason());
    }

    #[Test]
    public function it_returns_correct_reason_from_sick_leave_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::sickLeave();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('sick_leave', $reason->getReason());
        $this->assertEquals('Sick leave', $reason->getDisplayValue());
    }

    #[Test]
    public function it_returns_correct_reason_from_time_off_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::timeOff();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('time_off', $reason->getReason());
    }

    #[Test]
    public function it_returns_correct_reason_from_time_off_in_lieu_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::timeOffInLieu();

        /* Assert: getReason() must be 'time_off_in_lieu', distinct from plain
         * 'time_off' — the values() array previously constructed this entry
         * with the wrong reason constant, making it indistinguishable from
         * TIME_OFF and breaking fromStatus('time_off_in_lieu'). */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('time_off_in_lieu', $reason->getReason());
        $this->assertEquals('Time off in lieu', $reason->getDisplayValue());
    }

    #[Test]
    public function it_returns_correct_reason_from_personal_leave_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::personalLeave();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('personal_leave', $reason->getReason());
        $this->assertEquals('Personal leave', $reason->getDisplayValue());
    }

    #[Test]
    public function it_returns_correct_reason_from_flextime_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::flextime();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('flextime', $reason->getReason());
        $this->assertEquals('Flextime', $reason->getDisplayValue());
    }

    #[Test]
    public function it_returns_correct_reason_from_other_factory_method()
    {
        /* Arrange */

        /* Act */
        $reason = AbsenceReason::other();

        /* Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('other', $reason->getReason());
        $this->assertEquals('Other', $reason->getDisplayValue());
    }

    #[Test]
    public function it_to_string_returns_reason_value()
    {
        /* Arrange */
        $reason = AbsenceReason::vacation();

        /* Act */
        $stringValue = (string) $reason;

        /* Assert */
        $this->assertEquals('vacation', $stringValue);
    }

    #[Test]
    public function it_values_are_cached_and_returns_same_instances()
    {
        /* Arrange */

        /* Act */
        $first  = AbsenceReason::values();
        $second = AbsenceReason::values();

        /* Assert */
        $this->assertSame($first, $second);
    }

    #[Test]
    public function it_verifies_time_off_in_lieu_has_a_distinct_reason_from_time_off()
    {
        /* Arrange */

        /* Act */
        $timeOff       = AbsenceReason::timeOff();
        $timeOffInLieu = AbsenceReason::timeOffInLieu();

        /* Assert */
        $this->assertNotEquals($timeOff->getReason(), $timeOffInLieu->getReason());
        $this->assertNotEquals($timeOff->getDisplayValue(), $timeOffInLieu->getDisplayValue());
    }

    #[Test]
    public function it_throws_exception_if_reason_is_not_known()
    {
        /* Arrange */
        $invalidReason = 'non_existing_reason';
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        AbsenceReason::fromStatus($invalidReason);

        /* Assert */
    }

    #[Test]
    public function it_from_status_is_case_sensitive()
    {
        /* Arrange */
        $wrongCase = 'Vacation';
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        AbsenceReason::fromStatus($wrongCase);

        /* Assert */
    }
}
