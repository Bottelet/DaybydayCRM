<?php

use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\PermissionRole;
use App\Models\Client;
use App\Models\Department;
use App\Models\Task;
use App\Models\Lead;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    protected $client;
    protected $role;
    protected $faker;
    protected $user;
    protected $department;
    protected $task;
    protected $lead;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Create a test client
     * @return Object
     */
    public function createClient()
    {
        //Create test client
        $this->client = New Client;
        $this->client->name = $this->faker->name;
        $this->client->company_name = $this->faker->company('name');
        $this->client->email = $this->faker->email;
        $this->client->industry_id = $this->faker->numberBetween($min = 1, $max = 25);
        $this->client->user_id = $this->user->id;
        $this->client->save();
    }

    /**
     * Create a test user
     * @return Object
     */
    public function createUser()
    {
        $this->user = new User;
        $this->user->name = 'Casper';
        $this->user->email = 'bottelet@flarepoint.com';
        $this->user->address = $this->faker->address;
        $this->user->password = bcrypt('admin');
        $this->user->save();
    }

    /**
     * Create a test role
     * @return Object
     */
    public function createRole()
    {
        $this->role = new Role;
        $this->role->display_name = 'Test role';
        $this->role->name = 'Test Role';
        $this->role->description = 'Role for testing';
        $this->role->save();

        $this->assignUserToRole();
    }

    /**
     * Create a test department
     * @return Object
     */
    public function createDepartment()
    {
        $this->department = new Department;
        $this->department->name = 'Test Department';
        $this->department->save();

        $this->assignUserToDepartment();
    }

    /**
     * Create a test task
     * @return Object
     */
    public function createTask($status, $user_id = null, $client_id = null)
    {
        if ($user_id == null) {
            $user_id = $this->user->id;
        }
        if ($client_id == null) {
            $client_id = $this->client->id;
        }

        $this->task = new Task;
        $this->task->title = $this->faker->sentence(3);
        $this->task->description = $this->faker->paragraphs(2, true);
        $this->task->deadline = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $this->task->client_id = $client_id;
        $this->task->user_assigned_id = $user_id;
        $this->task->user_created_id = $user_id;
        $this->task->status = ($status == 'open' ? 1 : 2);
        $this->task->save();
    }

    /**
     * Create a test lead
     * @return Object
     */
    public function createLead($status, $user_id = null, $client_id = null)
    {
        if ($user_id == null) {
            $user_id = $this->user->id;
        }
        if ($client_id == null) {
            $client_id = $this->client->id;
        }

        $this->lead = new Lead;
        $this->lead->title = $this->faker->sentence(3);
        $this->lead->note = $this->faker->paragraphs(2, true);
        $this->lead->contact_date = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $this->lead->client_id = $client_id;
        $this->lead->user_assigned_id = $user_id;
        $this->lead->user_created_id = $user_id;
        $this->lead->status = ($status == 'open' ? 1 : 2);
        $this->lead->save();
    }


    protected function assignUserToRole()
    {
        $newrole = new RoleUser;
        $newrole->role_id = $this->role->id;
        $newrole->user_id = $this->user->id;
        $newrole->timestamps = false;
        $newrole->save();
    }

    protected function assignUserToDepartment()
    {
        \DB::table('department_user')->insert([
            'department_id' => $this->department->id,
            'user_id' => $this->user->id
        ]);
    }

    /**
     * Permissions
     */
    public function createUserPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '1';
        $createUser->timestamps = false;
        $createUser->save();

    }

    public function updateUserPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '2';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function deleteUserPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '3';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function createClientPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '4';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function updateClientPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '5';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function deleteClientPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '6';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function createTaskPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '7';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function updateTaskPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '8';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function createLeadPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '9';
        $createUser->timestamps = false;
        $createUser->save();
    }

    public function updateLeadPermission()
    {
        $createUser = new PermissionRole;
        $createUser->role_id = $this->role->id;
        $createUser->permission_id = '10';
        $createUser->timestamps = false;
        $createUser->save();
    }
}
