<?php

namespace Tests\Unit\Client;

use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use App\Services\ClientNumber\ClientNumberService;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientNumberServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected $client;

    /**
     * @var Application
     */
    private $clientNumberService;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::factory()->create(); // Ensure Setting exists

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create([

            'company_name' => 'Just something',
        ]);

        $this->clientNumberService = app(ClientNumberService::class);
        $this->clientNumberService->setClientNumber('980200');
    }

    #[Test]
    public function set_next_client_number_takes_biggest_client_number_and_add_one()
    {
        $this->assertEquals(980200, $this->clientNumberService->setNextClientNumber());
        $this->assertEquals(980201, $this->clientNumberService->setNextClientNumber());
    }

    #[Test]
    public function next_client_number_takes_biggest_client_number_and_does_not_set_it()
    {
        $this->assertEquals(980200, $this->clientNumberService->nextClientNumber());
        $this->assertEquals(980200, $this->clientNumberService->nextClientNumber());
    }

    #[Test]
    public function manually_set_next_client_number()
    {
        $this->clientNumberService->setClientNumber(20000);
        $this->assertEquals(20000, $this->clientNumberService->nextClientNumber());
    }
}
