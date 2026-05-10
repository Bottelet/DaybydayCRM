<?php

namespace Tests\Unit\Client;

use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use App\Services\ClientNumber\ClientNumberService;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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

        $this->user   = User::factory()->create();
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

    # region happy_path

    #[Test]
    public function it_sets_next_client_number_and_increments_it()
    {
        /** Arrange */
        // Service initialized with 980200 in setUp()

        /** Act */
        $firstNumber  = $this->clientNumberService->setNextClientNumber();
        $secondNumber = $this->clientNumberService->setNextClientNumber();

        /* Assert */
        $this->assertEquals(980200, $firstNumber);
        $this->assertEquals(980201, $secondNumber);
    }

    #[Test]
    public function it_returns_current_client_number_without_incrementing()
    {
        /** Arrange */
        // Service initialized with 980200 in setUp()

        /** Act */
        $firstNumber  = $this->clientNumberService->nextClientNumber();
        $secondNumber = $this->clientNumberService->nextClientNumber();

        /* Assert */
        $this->assertEquals(980200, $firstNumber);
        $this->assertEquals(980200, $secondNumber);
    }

    #[Test]
    public function it_allows_manually_setting_client_number()
    {
        /** Arrange */
        $newNumber = 20000;

        /* Act */
        $this->clientNumberService->setClientNumber($newNumber);
        $result = $this->clientNumberService->nextClientNumber();

        /* Assert */
        $this->assertEquals(20000, $result);
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_starts_incrementing_sequence_from_zero_when_set_to_zero()
    {
        /* Arrange */
        $this->clientNumberService->setClientNumber(0);

        /** Act */
        $firstClient  = $this->clientNumberService->setNextClientNumber();
        $secondClient = $this->clientNumberService->setNextClientNumber();

        /* Assert */
        // Setting the client number to 0 starts the sequence at 0
        // and subsequent calls continue incrementing without duplicates.
        $this->assertEquals(0, $firstClient);
        $this->assertEquals(1, $secondClient);
        $this->assertNotEquals($firstClient, $secondClient, 'Client numbers should not duplicate');
    }

    #[Test]
    public function it_sets_very_large_client_number()
    {
        /** Arrange */
        $largeNumber = 99999999;

        /* Act */
        $this->clientNumberService->setClientNumber($largeNumber);
        $result = $this->clientNumberService->setNextClientNumber();

        /* Assert */
        $this->assertEquals(99999999, $result);
    }

    #[Test]
    public function it_increments_from_zero()
    {
        /* Arrange */
        $this->clientNumberService->setClientNumber(0);

        /** Act */
        $firstNumber  = $this->clientNumberService->setNextClientNumber();
        $secondNumber = $this->clientNumberService->setNextClientNumber();

        /* Assert */
        $this->assertEquals(0, $firstNumber);
        $this->assertEquals(1, $secondNumber);
    }

    # endregion

    # region failure_path

    #[Test]
    public function it_rejects_negative_client_numbers()
    {
        /** Arrange */
        $negativeNumber = -100;

        /* Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client number cannot be negative.');

        /* Act */
        $this->clientNumberService->setClientNumber($negativeNumber);
    }

    # endregion
}
