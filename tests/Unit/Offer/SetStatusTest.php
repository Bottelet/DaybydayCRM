<?php

namespace Tests\Unit\Offer;

use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class SetStatusTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Offer */
    protected $offer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->offer = Offer::factory()->create();
    }

    // region happy_path

    #[Test]
    public function set_offer_as_won()
    {
        /** Arrange */
        // Offer already created in setUp()

        /** Act */
        $this->offer->setAsWon();

        /** Assert */
        $this->assertEquals('won', $this->offer->status);
    }

    #[Test]
    public function set_offer_as_lost()
    {
        /** Arrange */
        // Offer already created in setUp()

        /** Act */
        $this->offer->setAsLost();

        /** Assert */
        $this->assertEquals('lost', $this->offer->status);
    }

    // endregion
}
