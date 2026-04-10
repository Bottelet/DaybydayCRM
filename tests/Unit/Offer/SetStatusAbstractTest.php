<?php

namespace Tests\Unit\Offer;

use App\Models\Offer;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SetStatusAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $offer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->offer = Offer::factory()->create();
    }

    #[Test]
    public function set_offer_as_won()
    {
        $this->assertNotEquals('won', $this->offer->status);
        $this->offer->setAsWon();

        $this->assertEquals('won', $this->offer->status);
    }

    #[Test]
    public function set_offer_as_list()
    {
        $this->assertNotEquals('lost', $this->offer->status);
        $this->offer->setAsLost();

        $this->assertEquals('lost', $this->offer->status);
    }
}
