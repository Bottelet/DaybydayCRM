<?php

namespace Database\Seeders\Concerns;

use App\Enums\OfferStatus;
use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * WorldBuilder.
 *
 * Extracted from DemoTableSeeder::createData() and the individual Dummy seeders.
 * Both DemoTableSeeder and DummyDatabaseSeeder use this trait so the relational
 * graph logic lives in exactly one place.
 *
 * The $sparse flag lets DummyDatabaseSeeder (Playwright / CI) skip optional
 * relations that would slow the seed down without adding test coverage value.
 */
trait WorldBuilder
{
    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    /**
     * Create factory users per role + optional named (deterministic) accounts.
     *
     * @param array<string, int> $perRole    e.g. ['manager' => 3, 'employee' => 8]
     * @param array<int, array>  $namedUsers e.g. [['name'=>…,'email'=>…,'password'=>…,'role'=>…]]
     */
    protected function createUsers(array $perRole, array $namedUsers = []): Collection
    {
        $users       = collect();
        $departments = Department::all();

        // Named / deterministic users first
        foreach ($namedUsers as $attrs) {
            $roleName = $attrs['role'] ?? 'employee';
            $role     = Role::where('name', $roleName)->firstOrFail();

            $user = User::factory()->create([
                'external_id' => Uuid::uuid4()->toString(),
                'name'        => $attrs['name'],
                'email'       => $attrs['email'],
                'password'    => bcrypt($attrs['password'] ?? 'password'),
            ]);

            $user->roles()->syncWithoutDetaching([$role->id]);
            $this->attachDepartment($user, $departments);
            $users->push($user);
        }

        // Bulk factory users per role
        foreach ($perRole as $roleName => $count) {
            $role = Role::where('name', $roleName)->firstOrFail();

            User::factory()->count($count)->create()->each(
                function (User $user) use ($role, $departments, $users) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                    $this->attachDepartment($user, $departments);

                    if (rand(1, 5) === 1) {
                        Absence::factory()->create(['user_id' => $user->id]);
                    }

                    $users->push($user);
                }
            );
        }

        // Guarantee one user with a current absence (useful for dashboard assertions)
        if ($users->isNotEmpty()) {
            $last = $users->last();
            Absence::query()->firstOrCreate(
                ['user_id' => $last->id, 'start_at' => now()->subDays(2)->toDateString()],
                ['end_at' => now()->addDays(1)->toDateString()]
            );
        }

        return $users;
    }

    // -------------------------------------------------------------------------
    // Client tree  (the old DemoTableSeeder::createData, now shared)
    // -------------------------------------------------------------------------

    /**
     * For every user, create $clientsPerUser clients and hang the full
     * relational tree off each one.
     *
     * @param Collection<User> $users
     */
    protected function createClientTree(
        Collection $users,
        int $clientsPerUser = 3,
        int $projectsPerClient = 2,
        int $tasksPerClient = 8,
        int $leadsPerClient = 5,
        int $commentsPerItem = 3,
        bool $sparse = false,
    ): Collection {
        $products   = Product::all();
        $allClients = collect();

        foreach ($users as $user) {
            Client::factory()
                ->count($clientsPerUser)
                ->create(['user_id' => $user->id])
                ->each(function (Client $client) use (
                    $user,
                    $projectsPerClient,
                    $tasksPerClient,
                    $leadsPerClient,
                    $commentsPerItem,
                    $sparse,
                    $products,
                    $allClients
                ) {
                    $allClients->push($client);

                    // Contact
                    Contact::factory()->create(['client_id' => $client->id]);

                    // Projects
                    $projects = $this->buildProjects($client, $user, $projectsPerClient, $commentsPerItem);

                    // Tasks
                    $this->buildTasks($client, $user, $projects, $tasksPerClient, $commentsPerItem, $sparse);

                    // Leads → Offers
                    $this->buildLeads($client, $user, $leadsPerClient, $commentsPerItem, $products, $sparse);
                });
        }

        return $allClients;
    }

    private function attachDepartment(User $user, Collection $departments): void
    {
        if ($departments->isEmpty()) {
            return;
        }
        DB::table('department_user')->insertOrIgnore([
            'department_id' => $departments->random()->id,
            'user_id'       => $user->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Projects
    // -------------------------------------------------------------------------

    private function buildProjects(
        Client $client,
        User $user,
        int $count,
        int $commentsPerItem
    ): Collection {
        return Project::factory()->count($count)->create([
            'client_id'        => $client->id,
            'user_created_id'  => $user->id,
            'user_assigned_id' => $user->id,
        ])->each(function (Project $project) use ($user, $commentsPerItem) {
            Comment::factory()->count($commentsPerItem)->create([
                'source_type' => Project::class,
                'source_id'   => $project->id,
                'user_id'     => $user->id,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Tasks
    // -------------------------------------------------------------------------

    private function buildTasks(
        Client $client,
        User $user,
        Collection $projects,
        int $count,
        int $commentsPerItem,
        bool $sparse
    ): void {
        Task::factory()->count($count)->create([
            'client_id'        => $client->id,
            'user_created_id'  => $user->id,
            'user_assigned_id' => $user->id,
            'project_id'       => $projects->isNotEmpty() ? $projects->random()->id : null,
        ])->each(function (Task $task) use ($user, $commentsPerItem, $sparse) {
            Comment::factory()->count($commentsPerItem)->create([
                'source_type' => Task::class,
                'source_id'   => $task->id,
                'user_id'     => $user->id,
            ]);

            // Invoice + Appointment: always in demo mode, randomly in sparse mode
            if ( ! $sparse || rand(1, 5) === 1) {
                Appointment::factory()->create([
                    'client_id' => $task->client_id,
                    'user_id'   => $user->id,
                    'source_id' => $task->id,
                ]);

                $invoice = Invoice::factory()->create([
                    'client_id'   => $task->client_id,
                    'source_id'   => $task->id,
                    'source_type' => Task::class,
                ]);

                InvoiceLine::factory()->count(rand(2, 5))->create([
                    'invoice_id' => $invoice->id,
                ]);

                Comment::factory()->count($commentsPerItem)->create([
                    'source_type' => Task::class,
                    'source_id'   => $task->id,
                    'user_id'     => $user->id,
                ]);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Leads → Offers → InvoiceLines
    // -------------------------------------------------------------------------

    private function buildLeads(
        Client $client,
        User $user,
        int $count,
        int $commentsPerItem,
        Collection $products,
        bool $sparse
    ): void {
        Lead::factory()->count($count)->create([
            'client_id'        => $client->id,
            'user_created_id'  => $user->id,
            'user_assigned_id' => $user->id,
        ])->each(function (Lead $lead) use ($user, $commentsPerItem, $products, $sparse) {
            Comment::factory()->count($commentsPerItem)->create([
                'source_type' => Lead::class,
                'source_id'   => $lead->id,
                'user_id'     => $user->id,
            ]);

            Offer::factory()->count($sparse ? 1 : rand(1, 3))->create([
                'status'      => OfferStatus::inProgress()->getStatus(),
                'source_id'   => $lead->id,
                'client_id'   => $lead->client_id,
                'source_type' => Lead::class,
            ])->each(function (Offer $offer) use ($products) {
                InvoiceLine::factory()->count(rand(1, 5))->create([
                    'offer_id'   => $offer->id,
                    'product_id' => ($products->isNotEmpty() && rand(1, 3) === 1)
                        ? $products->random()->id
                        : null,
                ]);
            });
        });
    }
}
