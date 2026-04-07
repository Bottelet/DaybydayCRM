<?php

namespace Tests\Unit\Enums;

use App\Enums\AbsenceReason;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AbsenceReasonTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function getting_reason_returns_instance_of_absence_reason()
    {
        $this->assertInstanceOf(AbsenceReason::class, AbsenceReason::fromStatus('vacation'));
    }

    #[Test]
    public function absence_reason_contains_both_reason_and_display_value()
    {
        $reason = AbsenceReason::fromStatus('vacation');
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    #[Test]
    public function get_display_value_from_reason()
    {
        $this->assertEquals('Vacation', AbsenceReason::fromStatus('vacation')->getDisplayValue());
    }

    #[Test]
    public function reason_returns_correct_reason_in_instance()
    {
        $this->assertEquals('sick_leave', AbsenceReason::sickLeave()->getReason());
    }

    #[Test]
    public function get_reason_from_display_value()
    {
        $this->assertEquals('vacation', AbsenceReason::fromDisplayValue('Vacation')->getReason());
    }

    #[Test]
    public function throws_exception_if_reason_is_not_known()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromStatus('non_existing_reason');
    }

    #[Test]
    public function throws_exception_if_display_value_is_not_known()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromDisplayValue('None existing display value');
    }

    #[Test]
    public function values_returns_all_eight_absence_reasons()
    {
        $values = AbsenceReason::values();
        $this->assertCount(8, $values);
    }

    #[Test]
    public function vacation_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::vacation();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    #[Test]
    public function vacation_day_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::vacationDay();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation_day', $reason->getReason());
    }

    #[Test]
    public function sick_leave_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::sickLeave();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('sick_leave', $reason->getReason());
        $this->assertEquals('Sick leave', $reason->getDisplayValue());
    }

    #[Test]
    public function time_off_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::timeOff();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('time_off', $reason->getReason());
    }

    #[Test]
    public function time_off_in_lieu_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::timeOffInLieu();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        // Note: the AbsenceReason for TIME_OFF_IN_LIEU is constructed with self::TIME_OFF as the reason string
        $this->assertEquals('time_off', $reason->getReason());
        $this->assertEquals('Time off in lieu', $reason->getDisplayValue());
    }

    #[Test]
    public function personal_leave_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::personalLeave();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('personal_leave', $reason->getReason());
        $this->assertEquals('Personal leave', $reason->getDisplayValue());
    }

    #[Test]
    public function flextime_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::flextime();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('flextime', $reason->getReason());
        $this->assertEquals('Flextime', $reason->getDisplayValue());
    }

    #[Test]
    public function other_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::other();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('other', $reason->getReason());
        $this->assertEquals('Other', $reason->getDisplayValue());
    }

    #[Test]
    public function to_string_returns_reason_value()
    {
        $reason = AbsenceReason::vacation();
        $this->assertEquals('vacation', (string) $reason);
    }

    #[Test]
    public function from_status_is_case_sensitive()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromStatus('Vacation');
    }

    #[Test]
    public function constructor_accepts_null_display_value()
    {
        $reason = new AbsenceReason('custom_reason', null);
        $this->assertEquals('custom_reason', $reason->getReason());
    }

    #[Test]
    public function from_display_value_is_case_sensitive()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromDisplayValue('vacation');
    }

    #[Test]
    public function from_display_value_throws_for_partial_match()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromDisplayValue('Vacatio');
    }

    #[Test]
    public function values_are_cached_and_returns_same_instances()
    {
        $first = AbsenceReason::values();
        $second = AbsenceReason::values();
        $this->assertSame($first, $second);
    }

    #[Test]
    public function time_off_in_lieu_shares_reason_string_with_time_off()
    {
        $timeOff = AbsenceReason::timeOff();
        $timeOffInLieu = AbsenceReason::timeOffInLieu();

        $this->assertEquals($timeOff->getReason(), $timeOffInLieu->getReason());
        $this->assertNotEquals($timeOff->getDisplayValue(), $timeOffInLieu->getDisplayValue());
    }
}
