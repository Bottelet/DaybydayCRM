<?php

namespace Tests\Unit\Enums;

use App\Enums\AbsenceReason;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AbsenceReasonTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function getting_reason_returns_instance_of_absence_reason()
    {
        $this->assertInstanceOf(AbsenceReason::class, AbsenceReason::fromStatus('vacation'));
    }

    /** @test */
    public function absence_reason_contains_both_reason_and_display_value()
    {
        $reason = AbsenceReason::fromStatus('vacation');
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    /** @test */
    public function get_display_value_from_reason()
    {
        $this->assertEquals('Vacation', AbsenceReason::fromStatus('vacation')->getDisplayValue());
    }

    /** @test */
    public function reason_returns_correct_reason_in_instance()
    {
        $this->assertEquals('sick_leave', AbsenceReason::sickLeave()->getReason());
    }

    /** @test */
    public function get_reason_from_display_value()
    {
        $this->assertEquals('vacation', AbsenceReason::fromDisplayValue('Vacation')->getReason());
    }

    /** @test */
    public function throws_exception_if_reason_is_not_known()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromStatus('non_existing_reason');
    }

    /** @test */
    public function throws_exception_if_display_value_is_not_known()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromDisplayValue('None existing display value');
    }

    /** @test */
    public function values_returns_all_eight_absence_reasons()
    {
        $values = AbsenceReason::values();
        $this->assertCount(8, $values);
    }

    /** @test */
    public function vacation_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::vacation();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    /** @test */
    public function vacation_day_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::vacationDay();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation_day', $reason->getReason());
    }

    /** @test */
    public function sick_leave_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::sickLeave();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('sick_leave', $reason->getReason());
        $this->assertEquals('Sick leave', $reason->getDisplayValue());
    }

    /** @test */
    public function time_off_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::timeOff();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('time_off', $reason->getReason());
    }

    /** @test */
    public function time_off_in_lieu_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::timeOffInLieu();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        // Note: the AbsenceReason for TIME_OFF_IN_LIEU is constructed with self::TIME_OFF as the reason string
        $this->assertEquals('time_off', $reason->getReason());
        $this->assertEquals('Time off in lieu', $reason->getDisplayValue());
    }

    /** @test */
    public function personal_leave_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::personalLeave();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('personal_leave', $reason->getReason());
        $this->assertEquals('Personal leave', $reason->getDisplayValue());
    }

    /** @test */
    public function flextime_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::flextime();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('flextime', $reason->getReason());
        $this->assertEquals('Flextime', $reason->getDisplayValue());
    }

    /** @test */
    public function other_factory_method_returns_correct_reason()
    {
        $reason = AbsenceReason::other();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('other', $reason->getReason());
        $this->assertEquals('Other', $reason->getDisplayValue());
    }

    /** @test */
    public function to_string_returns_reason_value()
    {
        $reason = AbsenceReason::vacation();
        $this->assertEquals('vacation', (string) $reason);
    }

    /** @test */
    public function from_status_is_case_sensitive()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromStatus('Vacation');
    }
}
