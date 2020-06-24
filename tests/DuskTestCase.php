<?php

namespace Tests;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Client;
use App\Models\Task;
use App\Models\Lead;
use App\Models\Contact;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    public function createUserWithRelations($attributes = [])
    {
        $user = factory(User::class)->create($attributes);
        
        $role = Role::whereName('factory')->first();
        if (!$role) {
            $role = factory(Role::class)->create();
        }
        $department = Department::whereName('factory')->first();
        if (!$department) {
            $department = factory(Department::class)->create();
        }

        $user->department()->attach($department->id);
        $user->attachRole($role);

        return $user;
    }

    public function createClientWithRelations($attributes = [])
    {
        $client = false;

        if (!array_has($attributes, 'user_id')) {
            $client = factory(Client::class)->create(array_merge($attributes, ['user_id' => User::whereEmail('admin@admin.com')->first()->id]));
        }

        if (!$client) {
            $client = factory(Client::class)->create($attributes);
        }

        if (!array_has($attributes, 'contact_id')) {
            $contact = factory(Contact::class)->create(['client_id' => $client->id]);
        }


        return ['client' => $client, 'contact' => $contact];
    }

    public function createTaskWithRelations($attributes = [])
    {
        if (!array_has($attributes, 'client_id')) {
            throw new \Exception("Client id is required");
        }
        if (!array_has($attributes, 'user_assigned_id') && !array_has($attributes, 'user_created_id')) {
            $user_id = User::whereEmail('admin@admin.com')->first()->id;
            $attributes = array_merge($attributes, ['user_assigned_id' => $user_id, 'user_created_id' => $user_id]);
        }
        
        $task = factory(Task::class)->create($attributes);

        return $task;
    }

    public function createLeadWithRelations($attributes = [])
    {
        if (!array_has($attributes, 'client_id')) {
            throw new \Exception("Client id is required");
        }

        if (!array_has($attributes, 'user_assigned_id') && !array_has($attributes, 'user_created_id')) {
            $user_id = User::whereEmail('admin@admin.com')->first()->id;
            $attributes = array_merge($attributes, ['user_assigned_id' => $user_id, 'user_created_id' => $user_id]);
        }

        $lead = factory(Lead::class)->create($attributes);
        

        return $lead;
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
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
