<?php
namespace Tests\Unit\Invoice;

use App\Enums\OfferStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Tests\TestCase;

class OfferStatusEnumTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    private $offerStatus;

    public function setUp(): void
    {
        parent::setUp();
        $this->offerStatus = OfferStatus::won()->getStatus();
    }

    /** @test */
    public function gettingSourceReturnsInstanceOfOfferStatus()
    {
        $this->assertInstanceOf(OfferStatus::class, OfferStatus::fromStatus($this->offerStatus));
    }

    /** @test */
    public function OfferStatusContainsBothDisplayAndSourceValue()
    {
        $this->assertObjectHasAttribute("status", OfferStatus::fromStatus($this->offerStatus));
        $this->assertObjectHasAttribute("displayValue", OfferStatus::fromStatus($this->offerStatus));
    }

    /** @test */
    public function getDisplayValueFromStatus()
    {
        $this->assertEquals(OfferStatus::fromStatus($this->offerStatus)->getDisplayValue(), "Won");
    }

    /** @test */
    public function sourceReturnsCorrectSourceInInstance()
    {
        $this->assertEquals("lost", OfferStatus::lost()->getStatus());
    }

    /** @test */
    public function getStatusFromDisplayValue()
    {
        $this->assertEquals(OfferStatus::fromDisplayValue("Won"), OfferStatus::won()->getStatus());
    }

    /** @test */
    public function throwsExceptionIfSourceIsNotKnown()
    {
        $this->expectException(\Exception::class);
        OfferStatus::fromStatus("None existing source");
    }

    /** @test */
    public function throwsExceptionIfDisplayValueIsNotKnown()
    {
        $this->expectException(\Exception::class);
        OfferStatus::fromDisplayValue("None existing display value");
    }
}
