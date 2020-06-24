<?php
namespace Tests\Unit\Lead;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConvertLeadToOrderTest extends TestCase
{
    use DatabaseTransactions;

    private $lead;

    public function setUp(): void
    {
        parent::setUp();
        $this->lead = factory(Lead::class)->create([
            'qualified' => false,
            'invoice_id' => null,
        ]);
    }

    /** @test */
    public function convertLeadToOrder()
    {
        $result = $this->lead->convertToOrder();
        $this->lead->refresh();
        $invoice = $this->lead->invoice;

        $this->assertEquals($result->id, $this->lead->invoice_id);
        $this->assertEquals("Closed", $this->lead->status->title);

        $this->assertEquals($invoice->external_id, $result->external_id);
        $this->assertEquals(get_class($invoice), get_class($result));

        $this->assertEquals($invoice->status, "draft");
    }

    /** @test */
    public function cantConvertAlreadyConvertedOrder()
    {
        $this->lead->invoice_id = factory(Invoice::class)->create()->id;

        $result = $this->lead->convertToOrder();
        $this->assertFalse($result);
    }

    /** @test */
    public function canConvertToOrder()
    {
        $this->assertTrue($this->lead->canConvertToOrder());

        $this->lead->invoice_id = factory(Invoice::class)->create()->id;
        $this->lead->save();
        $this->lead->refresh();

        $this->assertFalse($this->lead->canConvertToOrder());
    }


}
