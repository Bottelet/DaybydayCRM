<?php

namespace Tests\Unit\Offers;

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

    #[Test]
    public function it_sets_offer_as_won()
    {
        /* Arrange */

        /* Act */
        $this->offer->setAsWon();

        /* Assert */
        $this->assertEquals('won', $this->offer->status);
    }

    #[Test]
    public function it_sets_offer_as_lost()
    {
        /* Arrange */

        /* Act */
        $this->offer->setAsLost();

        /* Assert */
        $this->assertEquals('lost', $this->offer->status);
    }
}
