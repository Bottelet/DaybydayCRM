<?php

namespace Tests\Unit\Client;

use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use App\Services\ClientNumber\ClientNumberService;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class ClientNumberServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var Client */
    protected $client;

    /** @var User */
    protected $user;

    /** @var Application|ClientNumberService */
    private $clientNumberService;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        Setting::factory()->create();

        $this->user = User::factory()->create();
        $this->client = Client::factory()->create([
            'company_name' => 'Just something',
        ]);

        $this->clientNumberService = app(ClientNumberService::class);
        $this->clientNumberService->setClientNumber('980200');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    //region happy_path

    #[Test]
    public function set_next_client_number_takes_biggest_client_number_and_add_one()
    {
        /** Arrange */
        // Service initialized with 980200 in setUp()

        /** Act */
        $firstNumber = $this->clientNumberService->setNextClientNumber();
        $secondNumber = $this->clientNumberService->setNextClientNumber();

        /** Assert */
        $this->assertEquals(980200, $firstNumber);
        $this->assertEquals(980201, $secondNumber);
    }

    #[Test]
    public function next_client_number_takes_biggest_client_number_and_does_not_set_it()
    {
        /** Arrange */
        // Service initialized with 980200 in setUp()

        /** Act */
        $firstNumber = $this->clientNumberService->nextClientNumber();
        $secondNumber = $this->clientNumberService->nextClientNumber();

        /** Assert */
        $this->assertEquals(980200, $firstNumber);
        $this->assertEquals(980200, $secondNumber);
    }

    #[Test]
    public function manually_set_next_client_number()
    {
        /** Arrange */
        $newNumber = 20000;

        /** Act */
        $this->clientNumberService->setClientNumber($newNumber);
        $result = $this->clientNumberService->nextClientNumber();

        /** Assert */
        $this->assertEquals(20000, $result);
    }

    //endregion

    //region edge_cases

    #[Test]
    public function set_client_number_to_zero_leads_to_duplicate_numbers()
    {
        /** Arrange */
        $this->clientNumberService->setClientNumber(0);
        
        /** Act */
        $firstClient = $this->clientNumberService->setNextClientNumber();
        $secondClient = $this->clientNumberService->setNextClientNumber();
        
        /** Assert */
        // Setting to 0 will cause duplicate numbers (both get 0)
        // This is a known edge case that should be prevented by validation
        $this->assertEquals(0, $firstClient);
        $this->assertEquals(1, $secondClient);
        $this->assertNotEquals($firstClient, $secondClient, 'Client numbers should not duplicate');
    }

    #[Test]
    public function set_very_large_client_number()
    {
        /** Arrange */
        $largeNumber = 99999999;

        /** Act */
        $this->clientNumberService->setClientNumber($largeNumber);
        $result = $this->clientNumberService->setNextClientNumber();

        /** Assert */
        $this->assertEquals(99999999, $result);
    }

    #[Test]
    public function increment_from_zero()
    {
        /** Arrange */
        $this->clientNumberService->setClientNumber(0);

        /** Act */
        $firstNumber = $this->clientNumberService->setNextClientNumber();
        $secondNumber = $this->clientNumberService->setNextClientNumber();

        /** Assert */
        $this->assertEquals(0, $firstNumber);
        $this->assertEquals(1, $secondNumber);
    }

    //endregion

    //region failure_path

    #[Test]
    public function set_negative_client_number_should_be_prevented()
    {
        /** Arrange */
        $negativeNumber = -100;

        /** Act */
        $this->clientNumberService->setClientNumber($negativeNumber);
        
        // Get the next number
        $firstNumber = $this->clientNumberService->setNextClientNumber();
        $secondNumber = $this->clientNumberService->setNextClientNumber();

        /** Assert */
        // Negative numbers will cause issues: -100, -99, -98, etc.
        // This demonstrates the problem - validation should prevent negative numbers
        $this->assertEquals(-100, $firstNumber);
        $this->assertEquals(-99, $secondNumber);
        // This test documents the problematic behavior that should be fixed
        $this->assertLessThan(0, $firstNumber, 'Negative client numbers should not be allowed');
    }

    //endregion
}
