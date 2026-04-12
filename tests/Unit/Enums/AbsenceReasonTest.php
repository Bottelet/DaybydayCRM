<?php

namespace Tests\Unit\Enums;

use App\Enums\AbsenceReason;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;

class AbsenceReasonTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function getting_reason_returns_instance_of_absence_reason()
    {
        /** Arrange */
        $statusValue = 'vacation';

        /** Act */
        $result = AbsenceReason::fromStatus($statusValue);

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $result);
    }

    #[Test]
    public function absence_reason_contains_both_reason_and_display_value()
    {
        /** Arrange */
        $statusValue = 'vacation';

        /** Act */
        $reason = AbsenceReason::fromStatus($statusValue);

        /** Assert */
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    #[Test]
    public function get_display_value_from_reason()
    {
        /** Arrange */
        $statusValue = 'vacation';

        /** Act */
        $displayValue = AbsenceReason::fromStatus($statusValue)->getDisplayValue();

        /** Assert */
        $this->assertEquals('Vacation', $displayValue);
    }

    #[Test]
    public function reason_returns_correct_reason_in_instance()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::sickLeave()->getReason();

        /** Assert */
        $this->assertEquals('sick_leave', $reason);
    }

    #[Test]
    public function get_reason_from_display_value()
    {
        /** Arrange */
        $displayValue = 'Vacation';

        /** Act */
        $reason = AbsenceReason::fromDisplayValue($displayValue)->getReason();

        /** Assert */
        $this->assertEquals('vacation', $reason);
    }

    #[Test]
    public function values_returns_all_eight_absence_reasons()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $values = AbsenceReason::values();

        /** Assert */
        $this->assertCount(8, $values);
    }

    #[Test]
    public function vacation_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::vacation();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    #[Test]
    public function vacation_day_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::vacationDay();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation_day', $reason->getReason());
    }

    #[Test]
    public function sick_leave_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::sickLeave();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('sick_leave', $reason->getReason());
        $this->assertEquals('Sick leave', $reason->getDisplayValue());
    }

    #[Test]
    public function time_off_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::timeOff();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('time_off', $reason->getReason());
    }

    #[Test]
    public function time_off_in_lieu_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::timeOffInLieu();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        // Note: the AbsenceReason for TIME_OFF_IN_LIEU is constructed with self::TIME_OFF as the reason string
        $this->assertEquals('time_off', $reason->getReason());
        $this->assertEquals('Time off in lieu', $reason->getDisplayValue());
    }

    #[Test]
    public function personal_leave_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::personalLeave();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('personal_leave', $reason->getReason());
        $this->assertEquals('Personal leave', $reason->getDisplayValue());
    }

    #[Test]
    public function flextime_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::flextime();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('flextime', $reason->getReason());
        $this->assertEquals('Flextime', $reason->getDisplayValue());
    }

    #[Test]
    public function other_factory_method_returns_correct_reason()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $reason = AbsenceReason::other();

        /** Assert */
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('other', $reason->getReason());
        $this->assertEquals('Other', $reason->getDisplayValue());
    }

    #[Test]
    public function to_string_returns_reason_value()
    {
        /** Arrange */
        $reason = AbsenceReason::vacation();

        /** Act */
        $stringValue = (string) $reason;

        /** Assert */
        $this->assertEquals('vacation', $stringValue);
    }

    #[Test]
    public function values_are_cached_and_returns_same_instances()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $first = AbsenceReason::values();
        $second = AbsenceReason::values();

        /** Assert */
        $this->assertSame($first, $second);
    }

    #[Test]
    public function time_off_in_lieu_shares_reason_string_with_time_off()
    {
        /** Arrange */
        // No arrangement needed

        /** Act */
        $timeOff = AbsenceReason::timeOff();
        $timeOffInLieu = AbsenceReason::timeOffInLieu();

        /** Assert */
        $this->assertEquals($timeOff->getReason(), $timeOffInLieu->getReason());
        $this->assertNotEquals($timeOff->getDisplayValue(), $timeOffInLieu->getDisplayValue());
    }

    // endregion

    // region edge_cases

    #[Test]
    public function constructor_accepts_null_display_value()
    {
        /** Arrange */
        $reasonValue = 'custom_reason';
        $displayValue = null;

        /** Act */
        $reason = new AbsenceReason($reasonValue, $displayValue);

        /** Assert */
        $this->assertEquals('custom_reason', $reason->getReason());
    }

    // endregion

    // region failure_path

    #[Test]
    public function throws_exception_if_reason_is_not_known()
    {
        /** Arrange */
        $invalidReason = 'non_existing_reason';

        /** Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        AbsenceReason::fromStatus($invalidReason);
    }

    #[Test]
    public function throws_exception_if_display_value_is_not_known()
    {
        /** Arrange */
        $invalidDisplayValue = 'None existing display value';

        /** Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        AbsenceReason::fromDisplayValue($invalidDisplayValue);
    }

    #[Test]
    public function from_status_is_case_sensitive()
    {
        /** Arrange */
        $wrongCase = 'Vacation';

        /** Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        AbsenceReason::fromStatus($wrongCase);
    }

    #[Test]
    public function from_display_value_is_case_sensitive()
    {
        /** Arrange */
        $wrongCase = 'vacation';

        /** Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        AbsenceReason::fromDisplayValue($wrongCase);
    }

    #[Test]
    public function from_display_value_throws_for_partial_match()
    {
        /** Arrange */
        $partialMatch = 'Vacatio';

        /** Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        AbsenceReason::fromDisplayValue($partialMatch);
    }

    // endregion
}
