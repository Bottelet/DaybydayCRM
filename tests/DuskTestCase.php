<?php

namespace Tests;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     *
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    public function createUserWithRelations($attributes = [])
    {
        $user = User::factory()->create($attributes);

        $role = Role::whereName('factory')->first();
        if ( ! $role) {
            $role = Role::factory()->create();
        }
        $department = Department::whereName('factory')->first();
        if ( ! $department) {
            $department = Department::factory()->create();
        }

        $user->department()->attach($department->id);
        $user->attachRole($role);

        return $user;
    }

    public function createClientWithRelations($attributes = [])
    {
        $client = false;

        if ( ! array_has($attributes, 'user_id')) {
            $client = Client::factory()->create(array_merge($attributes, ['user_id' => User::whereEmail('admin@admin.com')->first()->id]));
        }

        if ( ! $client) {
            $client = Client::factory()->create($attributes);
        }

        if ( ! array_has($attributes, 'contact_id')) {
            $contact = Contact::factory()->create(['client_id' => $client->id]);
        }

        return ['client' => $client, 'contact' => $contact];
    }

    public function createTaskWithRelations($attributes = [])
    {
        if ( ! array_has($attributes, 'client_id')) {
            throw new Exception('Client id is required');
        }
        if ( ! array_has($attributes, 'user_assigned_id') && ! array_has($attributes, 'user_created_id')) {
            $user_id    = User::whereEmail('admin@admin.com')->first()->id;
            $attributes = array_merge($attributes, ['user_assigned_id' => $user_id, 'user_created_id' => $user_id]);
        }

        $task = Task::factory()->create($attributes);

        return $task;
    }

    public function createLeadWithRelations($attributes = [])
    {
        if ( ! array_has($attributes, 'client_id')) {
            throw new Exception('Client id is required');
        }

        if ( ! array_has($attributes, 'user_assigned_id') && ! array_has($attributes, 'user_created_id')) {
            $user_id    = User::whereEmail('admin@admin.com')->first()->id;
            $attributes = array_merge($attributes, ['user_assigned_id' => $user_id, 'user_created_id' => $user_id]);
        }

        $lead = Lead::factory()->create($attributes);

        return $lead;
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions())->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1920',
        ]);

        return RemoteWebDriver::create(
            'http://chrome:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }
}
