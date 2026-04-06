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

    protected function setUp(): void
    {
        parent::setUp();
        $this->paidStatus = InvoiceStatus::paid()->getStatus();
    }

    /** @test */
    public function getting_status_returns_instance_of_invoice_status()
    {
        $this->assertInstanceOf(InvoiceStatus::class, InvoiceStatus::fromStatus($this->paidStatus));
    }

    /** @test */
    public function invoice_status_contains_both_display_and_status_value()
    {
        $this->assertObjectHasAttribute('status', InvoiceStatus::fromStatus($this->paidStatus));
        $this->assertObjectHasAttribute('displayValue', InvoiceStatus::fromStatus($this->paidStatus));
    }

    /** @test */
    public function get_display_value_from_status()
    {
        $this->assertEquals(InvoiceStatus::fromStatus($this->paidStatus)->getDisplayValue(), 'Paid');
    }

    /** @test */
    public function status_returns_correct_status_in_instance()
    {
        $this->assertEquals(InvoiceStatus::draft()->getStatus(), 'draft');
    }

    /** @test */
    public function get_status_from_display_value()
    {
        $this->assertEquals(InvoiceStatus::fromDisplayValue('Partially paid'), InvoiceStatus::partialPaid()->getStatus());
    }

    /** @test
     */
    public function throws_exception_if_status_is_not_known()
    {
        $this->expectException(\Exception::class);
        InvoiceStatus::fromStatus('None existing status');
    }

    /** @test
     */
    public function throws_exception_if_display_value_is_not_known()
    {
        $this->expectException(\Exception::class);
        InvoiceStatus::fromDisplayValue('None existing display value');
    }
}
