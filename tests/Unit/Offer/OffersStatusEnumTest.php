<?php

namespace Tests\Unit\Offer;

use App\Enums\OfferStatus;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class OffersStatusEnumTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var string */
    private $offerStatus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->offerStatus = OfferStatus::won()->getStatus();
    }

    # region happy_path

    #[Test]
    public function getting_source_returns_instance_of_offer_status()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $result = OfferStatus::fromStatus($this->offerStatus);

        /** Assert */
        $this->assertInstanceOf(OfferStatus::class, $result);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function offer_status_contains_both_display_and_source_value()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $offerStatus = OfferStatus::fromStatus($this->offerStatus);

        /** Assert */
        $this->assertTrue(property_exists($offerStatus, 'status'));
        $this->assertTrue(property_exists($offerStatus, 'displayValue'));
    }

    #[Test]
    public function get_display_value_from_status()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $displayValue = OfferStatus::fromStatus($this->offerStatus)->getDisplayValue();

        /** Assert */
        $this->assertEquals('Won', $displayValue);
    }

    #[Test]
    public function source_returns_correct_source_in_instance()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $status = OfferStatus::lost()->getStatus();

        /** Assert */
        $this->assertEquals('lost', $status);
    }

    #[Test]
    public function get_status_from_display_value()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act */
        $status = OfferStatus::fromDisplayValue('Won');

        /** Assert */
        $this->assertEquals(OfferStatus::won()->getStatus(), $status);
    }

    # endregion

    # region failure_path

    #[Test]
    public function throws_exception_if_source_is_not_known()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act & Assert */
        $this->expectException(Exception::class);
        OfferStatus::fromStatus('None existing source');
    }

    #[Test]
    public function throws_exception_if_display_value_is_not_known()
    {
        /** Arrange */
        // No additional arrangement needed

        /** Act & Assert */
        $this->expectException(Exception::class);
        OfferStatus::fromDisplayValue('None existing display value');
    }

    # endregion
}
