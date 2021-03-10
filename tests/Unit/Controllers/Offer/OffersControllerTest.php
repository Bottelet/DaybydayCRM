<?php
namespace Tests\Unit\Controllers\Offer;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Lead;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Project;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Offer;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OffersControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $lead;
    protected $offer;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->lead = factory(Lead::class)->create();
        $this->offer = factory(Offer::class)->create();
    }

    /** @test **/
    public function can_create_offer()
    {
        $this->json('POST', route('create.offer', $this->lead->external_id), [
            'lines' => [
                'title' => 'test line',
                'price' => 1000,
                'quantity' => 2,
                'type' => 'pieces',
                'comment' => 'A comment',
                'product' => '',
            ]
        ]);
        
        $this->lead->refresh();

        $this->assertNotEmpty($this->lead->offers);
        $this->assertNotEmpty($this->lead->offers->first()->invoiceLines);

        $this->assertEquals($this->lead->offers->first()->source_id, $this->lead->id);
        $this->assertEquals($this->lead->offers->first()->source_type, Lead::class);

    }

    /** @test **/
    public function can_update_offer()
    {
        $this->assertCount(0, $this->offer->invoiceLines);
        $this->json('POST', route('offer.update', $this->offer->external_id), [
            'lines' => 
                [
                    'title' => 'test line',
                    'price' => 1000,
                    'quantity' => 4,
                    'type' => 'pieces',
                    'comment' => 'A comment',
                    'product' => '',
                ],
                [
                    'title' => 'test line',
                    'price' => 1000,
                    'quantity' => 4,
                    'type' => 'pieces',
                    'comment' => 'A comment',
                    'product' => '',
                ],
                [
                    'title' => 'test line',
                    'price' => 1000,
                    'quantity' => 4,
                    'type' => 'pieces',
                    'comment' => 'A comment',
                    'product' => '',
                ]
        ]);
        
        $this->offer->refresh();

        $this->assertCount(3, $this->offer->invoiceLines);
    }
    

    /** @test **/
    public function can_set_offer_as_won()
    {
        $this->assertEquals("in-progress", $this->offer->status);
        $this->json('POST', route('offer.won'), [
            'offer_external_id' => $this->offer->external_id
        ]);
        
        $this->offer->refresh();
            
        $this->assertEquals("won", $this->offer->status);
        $this->assertNotNull($this->offer->invoice);

    }

    /** @test **/
    public function can_set_offer_as_lost()
    {
        $this->assertEquals("in-progress", $this->offer->status);
        $this->json('POST', route('offer.lost'), [
            'offer_external_id' => $this->offer->external_id
        ]);
        
        $this->offer->refresh();
    
        $this->assertEquals("lost", $this->offer->status);
        $this->assertNull($this->offer->invoice);

    }
}
