<?php

namespace Tests\Unit\Offer;

use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SetStatusTest extends TestCase
{
    use DatabaseTransactions;

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
