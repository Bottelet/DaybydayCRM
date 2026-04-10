<?php

namespace Database\Seeders;

use App\Enums\OfferStatus;
use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Project;
use App\Models\RoleUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class DemoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'id' => 2,
            'external_id' => Uuid::uuid4(),
            'name' => 'DaybydayCRM',
            'email' => 'demo@daybydaycrm.com',
            'password' => bcrypt('Daybydaycrm123'),
        ])->each(function ($user) {
            $this->createData($user);
        });

        $newrole = new RoleUser();
        $newrole->role_id = '2';
        $newrole->user_id = '2';
        $newrole->timestamps = false;
        $newrole->save();

        DB::table('department_user')->insert([
            'department_id' => 1,
            'user_id' => 2,
        ]);

        User::factory()->count(4)->create()->each(function ($user) {
            if (rand(1, 4) == 3) {
                Absence::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
            $this->createData($user);
        });

        $u = User::query()->latest()->first();
        Absence::factory()->create([
            'user_id' => $u->id,
            'start_at' => now()->subDays(2),
            'end_at' => now()->addDays(1),
        ]);
    }

    private function createData(User $user)
    {
        Client::factory()->count(rand(1, 5))->create(['user_id' => $user->id])->each(function ($client) use ($user) {
            $project = null;
            if (rand(1, 3) == 2) {
                $project = Project::factory()->create([
                    'client_id' => $client->id,
                    'user_created_id' => $user->id,
                    'user_assigned_id' => $user->id,
                ]);
                Comment::factory()->count(rand(2, 6))->create([
                    'source_type' => Project::class,
                    'source_id' => $project->id,
                    'user_id' => $user->id,
                ]);
            }
            Task::factory()->count(rand(5, 13))->create([
                'client_id' => $client->id,
                'user_created_id' => $user->id,
                'user_assigned_id' => $user->id,
                'project_id' => optional($project)->id,
            ])->each(function ($task) use ($user) {
                if (rand(1, 5) == 1) {
                    Appointment::factory()->create([
                        'client_id' => $task->client_id,
                        'user_id' => $user->id,
                        'source_id' => $task->id,
                    ]);
                    $invoice = Invoice::factory()->create([
                        'client_id' => $task->client_id,
                        'source_id' => $task->id,
                        'source_type' => Task::class,
                    ]);
                    InvoiceLine::factory()->count(4)->create([
                        'invoice_id' => $invoice->id,
                    ]);

                    Comment::factory()->count(3)->create([
                        'source_type' => Task::class,
                        'source_id' => $task->id,
                        'user_id' => $user->id,
                    ]);
                }

                Comment::factory()->count(3)->create([
                    'source_type' => Task::class,
                    'source_id' => $task->id,
                    'user_id' => $user->id,
                ]);
            });

            Lead::factory()->count(rand(3, 7))->create([
                'client_id' => $client->id,
                'user_created_id' => $user->id,
                'user_assigned_id' => $user->id,
            ])->each(function ($lead) use ($user) {
                if (rand(0, 5) == 1) {
                    Comment::factory()->count(3)->create([
                        'source_type' => Lead::class,
                        'source_id' => $lead->id,
                        'user_id' => $user->id,
                    ]);
                }
                $offer = Offer::factory()->create([
                    'status' => OfferStatus::inProgress()->getStatus(),
                    'source_id' => $lead->id,
                    'client_id' => $lead->client_id,
                    'source_type' => Lead::class,
                ]);
                InvoiceLine::factory()->count(rand(1, 5))->create([
                    'offer_id' => $offer->id,
                    'product_id' => rand(1, 4) == 2 ? Product::factory()->create()->id : null,
                ]);
                Comment::factory()->count(2)->create([
                    'source_type' => Lead::class,
                    'source_id' => $lead->id,
                    'user_id' => $user->id,
                ]);
            });
        });
    }
}
