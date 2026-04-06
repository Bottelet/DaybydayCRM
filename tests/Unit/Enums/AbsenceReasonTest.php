<?php

namespace Tests\Unit\Enums;

use App\Enums\AbsenceReason;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AbsenceReasonTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function gettingReasonReturnsInstanceOfAbsenceReason()
    {
        $this->assertInstanceOf(AbsenceReason::class, AbsenceReason::fromStatus('vacation'));
    }

    /** @test */
    public function absenceReasonContainsBothReasonAndDisplayValue()
    {
        $reason = AbsenceReason::fromStatus('vacation');
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    /** @test */
    public function getDisplayValueFromReason()
    {
        $this->assertEquals('Vacation', AbsenceReason::fromStatus('vacation')->getDisplayValue());
    }

    /** @test */
    public function reasonReturnsCorrectReasonInInstance()
    {
        $this->assertEquals('sick_leave', AbsenceReason::sickLeave()->getReason());
    }

    /** @test */
    public function getReasonFromDisplayValue()
    {
        $this->assertEquals('vacation', AbsenceReason::fromDisplayValue('Vacation')->getReason());
    }

    /** @test */
    public function throwsExceptionIfReasonIsNotKnown()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromStatus('non_existing_reason');
    }

    /** @test */
    public function throwsExceptionIfDisplayValueIsNotKnown()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromDisplayValue('None existing display value');
    }

    /** @test */
    public function valuesReturnsAllEightAbsenceReasons()
    {
        $values = AbsenceReason::values();
        $this->assertCount(8, $values);
    }

    /** @test */
    public function vacationFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::vacation();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation', $reason->getReason());
        $this->assertEquals('Vacation', $reason->getDisplayValue());
    }

    /** @test */
    public function vacationDayFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::vacationDay();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('vacation_day', $reason->getReason());
    }

    /** @test */
    public function sickLeaveFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::sickLeave();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('sick_leave', $reason->getReason());
        $this->assertEquals('Sick leave', $reason->getDisplayValue());
    }

    /** @test */
    public function timeOffFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::timeOff();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('time_off', $reason->getReason());
    }

    /** @test */
    public function timeOffInLieuFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::timeOffInLieu();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        // Note: the AbsenceReason for TIME_OFF_IN_LIEU is constructed with self::TIME_OFF as the reason string
        $this->assertEquals('time_off', $reason->getReason());
        $this->assertEquals('Time off in lieu', $reason->getDisplayValue());
    }

    /** @test */
    public function personalLeaveFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::personalLeave();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('personal_leave', $reason->getReason());
        $this->assertEquals('Personal leave', $reason->getDisplayValue());
    }

    /** @test */
    public function flextimeFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::flextime();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('flextime', $reason->getReason());
        $this->assertEquals('Flextime', $reason->getDisplayValue());
    }

    /** @test */
    public function otherFactoryMethodReturnsCorrectReason()
    {
        $reason = AbsenceReason::other();
        $this->assertInstanceOf(AbsenceReason::class, $reason);
        $this->assertEquals('other', $reason->getReason());
        $this->assertEquals('Other', $reason->getDisplayValue());
    }

    /** @test */
    public function toStringReturnsReasonValue()
    {
        $reason = AbsenceReason::vacation();
        $this->assertEquals('vacation', (string) $reason);
    }

    /** @test */
    public function fromStatusIsCaseSensitive()
    {
        $this->expectException(\Exception::class);
        AbsenceReason::fromStatus('Vacation');
    }
}