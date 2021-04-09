<?php
namespace Tests\Unit\Format;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Format\GetDateFormat;
use Carbon\Carbon;
use Tests\TestCase;

class GetDateFormatTest extends TestCase
{
    /** @var $formatter GetDateFormat */
    protected $formatter;
    public function setUp():void
    {
        parent::setUp();

        Setting::first()->update(['country' => 'GB']);
        $this->formatter = app(GetDateFormat::class);
    }

    /** @test */
    public function happyPath()
    {
        $this->assertEquals("H:i", $this->formatter->getCarbonTime());
        $this->assertEquals("dd/mm/yyyy", $this->formatter->getFrontendDate());
        $this->assertEquals("HH:i", $this->formatter->getFrontendTime());
        $this->assertEquals("d, F Y H:i", $this->formatter->getCarbonFullDateWithText());
        $this->assertEquals("d/m/Y", $this->formatter->getCarbonDate());
    }

    /** @test */
    public function happyPathWithHelpers()
    {
        $this->assertEquals("H:i", carbonTime());
        $this->assertEquals("dd/mm/yyyy", frontendDate());
        $this->assertEquals("HH:i", frontendTime());
        $this->assertEquals("d, F Y H:i", carbonFullDateWithText());
        $this->assertEquals("d/m/Y", carbonDate());
    }

    /** @test */
    public function dateExpected()
    {
        $this->assertEquals("15:00", Carbon::parse("22-02-2020 15:00:00")->format($this->formatter->getCarbonTime()));
        $this->assertEquals("22/02/2020", Carbon::parse("22-02-2020 15:00:00")->format($this->formatter->getCarbonDate()));
        $this->assertEquals("22, February 2020 13:00", Carbon::parse("22-02-2020 13:00:00")->format($this->formatter->getCarbonFullDateWithText()));
    }
}
