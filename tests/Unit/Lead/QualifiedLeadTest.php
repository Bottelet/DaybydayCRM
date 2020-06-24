<?php
namespace Tests\Unit\Lead;

use App\Models\Lead;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QualifiedLeadTest extends TestCase
{
    use DatabaseTransactions;

    private $lead;

    public function setUp(): void
    {
        parent::setUp();
        $this->lead = factory(Lead::class)->make([
            'qualified' => false,
        ]);
    }

    /** @test */
    public function isQualified()
    {
        $this->assertFalse($this->lead->isQualified);
    }

    /** @test */
    public function canConvertNonQualifiedLeadToQualified()
    {
        $this->assertFalse($this->lead->isQualified);

        $this->lead->convertToQualified();

        $this->assertTrue($this->lead->isQualified);
    }

    /** @test */
    public function alreadyQualifiedLeadShouldNotGetUnqualified()
    {
        $this->lead->qualified = true;
        $this->assertTrue($this->lead->isQualified);

        $this->lead->convertToQualified();

        $this->assertTrue($this->lead->isQualified);
    }
}
