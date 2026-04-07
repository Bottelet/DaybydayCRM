<?php

namespace Tests\Unit\Invoice;

use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SetStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected $offer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->offer = factory(Offer::class)->create();
    }

    /** @test */
    public function set_offer_as_won()
    {
        $this->assertNotEquals('won', $this->offer->status);
        $this->offer->setAsWon();

        $this->assertEquals('won', $this->offer->status);
    }

    /** @test */
    public function set_offer_as_list()
    {
        $this->assertNotEquals('lost', $this->offer->status);
        $this->offer->setAsLost();

        $this->assertEquals('lost', $this->offer->status);
    }
}
