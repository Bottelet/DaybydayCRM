<?php

namespace Tests\Browser;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use Carbon\Carbon;

class AppointmentTest extends DuskTestCase
{
    public function testIcancreateanAppointmentInCalendar()
    {
        $title = "new appointment test " . uniqid();
        $this->browse(function (Browser $browser) use($title) {
            $browser->loginAs(User::whereEmail('admin@admin.com')->first())
                ->visit('/appointments/calendar')
                ->click("#wrapper > div > div.utility-bar > ul > li:nth-child(2) > div > button")
                ->waitForText("Create new appointment")
                ->type('title', $title)
                ->select('color', '#ffd6d6')
                ->press('Create')
                ->press('Close');
        });

        $this->assertDatabaseHas('appointments', [
            'title' => $title,
            'color' => '#ffd6d6'
        ]);
    }

}
