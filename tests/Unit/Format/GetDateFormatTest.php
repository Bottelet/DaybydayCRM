<?php

namespace Tests\Unit\Format;

use App\Models\Setting;
use App\Repositories\Format\GetDateFormat;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetDateFormatTest extends TestCase
{
    /** @var GetDateFormat */
    protected $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::first()->update(['country' => 'GB']);
        $this->formatter = app(GetDateFormat::class);
    }

    #[Test]
    public function happy_path()
    {
        $this->assertEquals('H:i', $this->formatter->getCarbonTime());
        $this->assertEquals('dd/mm/yyyy', $this->formatter->getFrontendDate());
        $this->assertEquals('HH:i', $this->formatter->getFrontendTime());
        $this->assertEquals('d, F Y H:i', $this->formatter->getCarbonFullDateWithText());
        $this->assertEquals('d/m/Y', $this->formatter->getCarbonDate());
    }

    #[Test]
    public function happy_path_with_helpers()
    {
        $this->assertEquals('H:i', carbonTime());
        $this->assertEquals('dd/mm/yyyy', frontendDate());
        $this->assertEquals('HH:i', frontendTime());
        $this->assertEquals('d, F Y H:i', carbonFullDateWithText());
        $this->assertEquals('d/m/Y', carbonDate());
    }

    #[Test]
    public function date_expected()
    {
        $this->assertEquals('15:00', Carbon::parse('22-02-2020 15:00:00')->format($this->formatter->getCarbonTime()));
        $this->assertEquals('22/02/2020', Carbon::parse('22-02-2020 15:00:00')->format($this->formatter->getCarbonDate()));
        $this->assertEquals('22, February 2020 13:00', Carbon::parse('22-02-2020 13:00:00')->format($this->formatter->getCarbonFullDateWithText()));
    }
}
