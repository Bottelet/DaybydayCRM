<?php

namespace Tests\Unit\Offer;

use App\Enums\OfferStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Exception;

class OffersStatusEnumTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var string
     */
    private $offerStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->offerStatus = OfferStatus::won()->getStatus();
    }

    #[Test]
    public function getting_source_returns_instance_of_offer_status()
    {
        $this->assertInstanceOf(OfferStatus::class, OfferStatus::fromStatus($this->offerStatus));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function offer_status_contains_both_display_and_source_value()
    {
        $this->assertTrue(property_exists(OfferStatus::fromStatus($this->offerStatus), 'status'));
        $this->assertTrue(property_exists(OfferStatus::fromStatus($this->offerStatus), 'displayValue'));
    }

    #[Test]
    public function get_display_value_from_status()
    {
        $this->assertEquals(OfferStatus::fromStatus($this->offerStatus)->getDisplayValue(), 'Won');
    }

    #[Test]
    public function source_returns_correct_source_in_instance()
    {
        $this->assertEquals('lost', OfferStatus::lost()->getStatus());
    }

    #[Test]
    public function get_status_from_display_value()
    {
        $this->assertEquals(OfferStatus::fromDisplayValue('Won'), OfferStatus::won()->getStatus());
    }

    #[Test]
    public function throws_exception_if_source_is_not_known()
    {
        $this->expectException(Exception::class);
        OfferStatus::fromStatus('None existing source');
    }

    #[Test]
    public function throws_exception_if_display_value_is_not_known()
    {
        $this->expectException(Exception::class);
        OfferStatus::fromDisplayValue('None existing display value');
    }
}
