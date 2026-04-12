<?php

namespace Tests\Unit\Format;

use App\Models\Setting;
use App\Models\User;
use App\Repositories\Format\GetDateFormat;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class GetDateFormatTest extends AbstractTestCase
{
    use RefreshDatabase;

    /** @var GetDateFormat */
    protected $formatter;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-15 12:00:00');

        Setting::factory()->create(['country' => 'DK']);
        $this->user = User::factory()->create(['language' => 'DK']);
        $this->actingAs($this->user);

        $this->formatter = app(GetDateFormat::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function get_date_format_methods_return_correct_formats()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $carbonTime = $this->formatter->getCarbonTime();
        $frontendDate = $this->formatter->getFrontendDate();
        $frontendTime = $this->formatter->getFrontendTime();
        $carbonFullDateWithText = $this->formatter->getCarbonFullDateWithText();
        $carbonDate = $this->formatter->getCarbonDate();

        /** Assert */
        $this->assertEquals('H:i', $carbonTime);
        $this->assertEquals('dd/mm/yyyy', $frontendDate);
        $this->assertEquals('HH:i', $frontendTime);
        $this->assertEquals('d, F Y H:i', $carbonFullDateWithText);
        $this->assertEquals('d/m/Y', $carbonDate);
    }

    #[Test]
    public function helper_functions_return_correct_formats()
    {
        /** Arrange */
        // Already arranged in setUp()

        /** Act */
        $carbonTime = carbonTime();
        $frontendDate = frontendDate();
        $frontendTime = frontendTime();
        $carbonFullDateWithText = carbonFullDateWithText();
        $carbonDate = carbonDate();

        /** Assert */
        $this->assertEquals('H:i', $carbonTime);
        $this->assertEquals('dd/mm/yyyy', $frontendDate);
        $this->assertEquals('HH:i', $frontendTime);
        $this->assertEquals('d, F Y H:i', $carbonFullDateWithText);
        $this->assertEquals('d/m/Y', $carbonDate);
    }

    #[Test]
    public function formats_carbon_dates_correctly()
    {
        /** Arrange */
        $testDate = Carbon::parse('22-02-2020 15:00:00');
        $testDate2 = Carbon::parse('22-02-2020 13:00:00');

        /** Act */
        $formattedTime = $testDate->format($this->formatter->getCarbonTime());
        $formattedDate = $testDate->format($this->formatter->getCarbonDate());
        $formattedFullDate = $testDate2->format($this->formatter->getCarbonFullDateWithText());

        /** Assert */
        $this->assertEquals('15:00', $formattedTime);
        $this->assertEquals('22/02/2020', $formattedDate);
        $this->assertEquals('22, February 2020 13:00', $formattedFullDate);
    }

    # endregion
}
