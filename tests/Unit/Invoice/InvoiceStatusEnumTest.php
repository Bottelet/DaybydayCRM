<?php
namespace Tests\Unit\Invoice;

use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InvoiceStatusEnumTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    private $paidStatus;

    public function setUp(): void
    {
        parent::setUp();
        $this->paidStatus = InvoiceStatus::paid()->getStatus();
    }

    /** @test */
    public function gettingStatusReturnsInstanceOfInvoiceStatus()
    {
        $this->assertInstanceOf(InvoiceStatus::class, InvoiceStatus::fromStatus($this->paidStatus));
    }

    /** @test */
    public function InvoiceStatusContainsBothDisplayAndStatusValue()
    {
        $this->assertObjectHasAttribute("status", InvoiceStatus::fromStatus($this->paidStatus));
        $this->assertObjectHasAttribute("displayValue", InvoiceStatus::fromStatus($this->paidStatus));
    }

    /** @test */
    public function getDisplayValueFromStatus()
    {
        $this->assertEquals(InvoiceStatus::fromStatus($this->paidStatus)->getDisplayValue(), "Paid");
    }

    /** @test */
    public function statusReturnsCorrectStatusInInstance()
    {
        $this->assertEquals(InvoiceStatus::draft()->getStatus(), "draft");
    }

    /** @test */
    public function getStatusFromDisplayValue()
    {
        $this->assertEquals(InvoiceStatus::fromDisplayValue("Partially paid"), InvoiceStatus::partialPaid()->getStatus());
    }

    /** @test
     */
    public function throwsExceptionIfStatusIsNotKnown()
    {
        $this->expectException(\Exception::class);
        InvoiceStatus::fromStatus("None existing status");
    }

    /** @test
     */
    public function throwsExceptionIfDisplayValueIsNotKnown()
    {
        $this->expectException(\Exception::class);
        InvoiceStatus::fromDisplayValue("None existing display value");
    }
}
