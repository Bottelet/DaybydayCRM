<?php

namespace Tests\Unit\Invoice;

use App\Enums\InvoiceStatus;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class InvoiceStatusEnumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    private $paidStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paidStatus = InvoiceStatus::paid()->getStatus();
    }

    #[Test]
    public function getting_status_returns_instance_of_invoice_status()
    {
        $this->assertInstanceOf(InvoiceStatus::class, InvoiceStatus::fromStatus($this->paidStatus));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function invoice_status_contains_both_display_and_status_value()
    {
        $this->assertTrue(property_exists(InvoiceStatus::fromStatus($this->paidStatus), 'status'));
        $this->assertTrue(property_exists(InvoiceStatus::fromStatus($this->paidStatus), 'displayValue'));
    }

    #[Test]
    public function get_display_value_from_status()
    {
        $this->assertEquals(InvoiceStatus::fromStatus($this->paidStatus)->getDisplayValue(), 'Paid');
    }

    #[Test]
    public function status_returns_correct_status_in_instance()
    {
        $this->assertEquals(InvoiceStatus::draft()->getStatus(), 'draft');
    }

    #[Test]
    public function get_status_from_display_value()
    {
        $this->assertEquals(InvoiceStatus::fromDisplayValue('Partially paid'), InvoiceStatus::partialPaid()->getStatus());
    }

    #[Test]
    public function throws_exception_if_status_is_not_known()
    {
        $this->expectException(Exception::class);
        InvoiceStatus::fromStatus('None existing status');
    }

    #[Test]
    public function throws_exception_if_display_value_is_not_known()
    {
        $this->expectException(Exception::class);
        InvoiceStatus::fromDisplayValue('None existing display value');
    }
}
