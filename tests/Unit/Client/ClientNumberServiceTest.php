<?php
namespace Tests\Unit\Client;

use App\Services\ClientNumber\ClientNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Client;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Testing\Fakes\EventFake;
use Tests\TestCase;

class ClientNumberServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $client;
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $clientNumberService;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->client = factory(Client::class)->create([

            'company_name' => 'Just something'
        ]);

        $this->clientNumberService = app(ClientNumberService::class);

    }

    /** @test */
    public function setNextClientNumberTakesBiggestClientNumberAndAddOne()
    {
        $this->assertEquals(10000, $this->clientNumberService->setNextClientNumber());
        $this->assertEquals(10001, $this->clientNumberService->setNextClientNumber());
    }

    /** @test */
    public function nextClientNumberTakesBiggestClientNumberAndDoesNotAddOne()
    {
        $this->assertEquals(10000, $this->clientNumberService->nextClientNumber());
        $this->assertEquals(10000, $this->clientNumberService->nextClientNumber());
    }

    /** @test */
    public function manuallySetNextClientNumber()
    {
        $this->clientNumberService->setClientNumber(20000);
        $this->assertEquals(20000, $this->clientNumberService->nextClientNumber());
    }
}
