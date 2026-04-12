<?php

namespace Tests\Unit\Invoice;

use App\Enums\InvoiceStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class InvoiceStatusEnumTest extends AbstractTestCase
{
    use RefreshDatabase;

    /**
     * @var string
     */
    private $paidStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->paidStatus = InvoiceStatus::paid()->getStatus();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function it_getting_status_returns_instance_of_invoice_status()
    {
        /** Arrange */
        // Paid status already set in setUp()

        /** Act */
        $result = InvoiceStatus::fromStatus($this->paidStatus);

        /** Assert */
        $this->assertInstanceOf(InvoiceStatus::class, $result);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function invoice_status_contains_both_display_and_status_value()
    {
        /** Arrange */
        // Paid status already set in setUp()

        /** Act */
        $status = InvoiceStatus::fromStatus($this->paidStatus);

        /** Assert */
        $this->assertTrue(property_exists($status, 'status'));
        $this->assertTrue(property_exists($status, 'displayValue'));
    }

    #[Test]
    public function it_gets_display_value_from_status()
    {
        /** Arrange */
        // Paid status already set in setUp()

        /** Act */
        $displayValue = InvoiceStatus::fromStatus($this->paidStatus)->getDisplayValue();

        /** Assert */
        $this->assertEquals('Paid', $displayValue);
    }

    #[Test]
    public function it_status_returns_correct_status_in_instance()
    {
        /** Arrange */
        // Using draft status

        /** Act */
        $status = InvoiceStatus::draft()->getStatus();

        /** Assert */
        $this->assertEquals('draft', $status);
    }

    #[Test]
    public function it_gets_status_from_display_value()
    {
        /** Arrange */
        $displayValue = 'Partially paid';

        /** Act */
        $status = InvoiceStatus::fromDisplayValue($displayValue);

        /** Assert */
        $this->assertEquals(InvoiceStatus::partialPaid()->getStatus(), $status);
    }

    // endregion

    // region edge_cases

    #[Test]
    public function it_all_status_types_have_display_values()
    {
        /** Arrange */
        $statuses = [
            InvoiceStatus::draft(),
            InvoiceStatus::unpaid(),
            InvoiceStatus::paid(),
            InvoiceStatus::partialPaid(),
            InvoiceStatus::overpaid(),
        ];

        /** Act & Assert */
        foreach ($statuses as $status) {
            $this->assertNotEmpty($status->getDisplayValue());
            $this->assertNotEmpty($status->getStatus());
        }
    }

    #[Test]
    public function it_gets_all_valid_statuses()
    {
        /** Arrange */
        $expectedStatuses = ['draft', 'unpaid', 'paid', 'partial_paid', 'overpaid'];

        /** Act */
        $actualStatuses = [
            InvoiceStatus::draft()->getStatus(),
            InvoiceStatus::unpaid()->getStatus(),
            InvoiceStatus::paid()->getStatus(),
            InvoiceStatus::partialPaid()->getStatus(),
            InvoiceStatus::overpaid()->getStatus(),
        ];

        /** Assert */
        $this->assertEquals($expectedStatuses, $actualStatuses);
    }

    // endregion

    // region failure_path

    #[Test]
    public function it_throws_exception_if_status_is_not_known()
    {
        /** Arrange */
        $invalidStatus = 'None existing status';

        /** Assert */
        $this->expectException(Exception::class);

        /** Act */
        InvoiceStatus::fromStatus($invalidStatus);
    }

    #[Test]
    public function it_throws_exception_if_display_value_is_not_known()
    {
        /** Arrange */
        $invalidDisplayValue = 'None existing display value';

        /** Assert */
        $this->expectException(Exception::class);

        /** Act */
        InvoiceStatus::fromDisplayValue($invalidDisplayValue);
    }

    #[Test]
    public function it_throws_exception_for_empty_status()
    {
        /** Arrange */
        $emptyStatus = '';

        /** Assert */
        $this->expectException(Exception::class);

        /** Act */
        InvoiceStatus::fromStatus($emptyStatus);
    }

    #[Test]
    public function it_throws_exception_for_null_display_value()
    {
        /** Arrange */
        $nullDisplayValue = null;

        /** Assert */
        $this->expectException(Exception::class);

        /** Act */
        InvoiceStatus::fromDisplayValue($nullDisplayValue);
    }

    // endregion
}
