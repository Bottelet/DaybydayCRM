<?php
namespace Tests\Unit\Invoice;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Tests\TestCase;

class SetStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected $offer;

    public function setUp(): void
    {
        parent::setUp();
        $this->offer = factory(Offer::class)->create();
    }

    /** @test */
    public function setOfferAsWon()
    {
        $this->assertNotEquals("won", $this->offer->status);
        $this->offer->setAsWon();

        $this->assertEquals("won", $this->offer->status);
    }

    /** @test */
    public function setOfferAsList()
    {
        $this->assertNotEquals("lost", $this->offer->status);
        $this->offer->setAsLost();

        $this->assertEquals("lost", $this->offer->status);
    }
}
