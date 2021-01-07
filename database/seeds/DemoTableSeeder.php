<?php

use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use App\Models\Absence;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Database\Seeder;

class DemoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createDep = new Department;
        $createDep->id = '2';
        $createDep->name = 'Nerds';
        $createDep->external_id = Uuid::uuid4();
        $createDep->external_id = Uuid::uuid4();
        $createDep->save();
        $createDep = new Department;
        $createDep->id = '3';
        $createDep->name = 'Genius';
        $createDep->external_id = Uuid::uuid4();
        $createDep->external_id = Uuid::uuid4();
        $createDep->save();

        $user = factory(User::class, 5)->create()->each(function ($user) {
            if(rand(1, 4) == 3) {
                factory(Absence::class)->create([
                    'user_id' => $user->id
                ]);
            }
            factory(App\Models\Client::class, rand(1,5))->create()->each(function ($client) use ($user) {
                $project = null;
                if(rand(1,4) == 3) {
                    $project = factory(Project::class)->create([
                        'client_id' => $client->id,
                        'user_created_id' => $user->id,
                        'user_assigned_id' => $user->id,
                    ]);
                    factory(App\Models\Comment::class, rand(2,6))->create([
                        'source_type' => Project::class,
                        'source_id' => $project->id,
                        'user_id' => $user->id,
                    ]);
                }
                factory(Task::class, rand(5,13))->create([
                    'client_id' => $client->id,
                    'user_created_id' => $user->id,
                    'user_assigned_id' => $user->id,
                    'project_id' => optional($project)->id
                    ])->each(function ($task) use ($user) {
                    if(rand(1,5) == 1) {
                        factory(Appointment::class)->create([
                            'client_id' => $task->client_id,
                            'user_id' => $user->id,
                            'source_id' => $task->id,
                        ]);
                        $invoice = factory(\App\Models\Invoice::class)->create([
                            'client_id' => $task->client_id
                        ]);
                        factory(\App\Models\InvoiceLine::class, 4)->create([
                            'invoice_id' => $invoice->id,
                        ]);
        
                        factory(App\Models\Comment::class, 3)->create([
                            'source_type' => Task::class,
                            'source_id' => $task->id,
                            'user_id' => $user->id,
                        ]);
                    }
        
                    factory(App\Models\Comment::class, 3)->create([
                        'source_type' => Task::class,
                        'source_id' => $task->id,
                        'user_id' => $user->id,
                    ]);
                });
        
                factory(Lead::class, rand(3,7))->create([
                    'client_id' => $client->id,
                    'user_created_id' => $user->id,
                    'user_assigned_id' => $user->id,
                ])->each(function ($lead) use ($user){
                    if(rand(0, 5) == 1) {
                        factory(App\Models\Comment::class, 3)->create([
                            'source_type' => Lead::class,
                            'source_id' => $lead->id,
                            'user_id' => $user->id,
                        ]);
                    }
                    factory(App\Models\Comment::class, 2)->create([
                        'source_type' => Lead::class,
                        'source_id' => $lead->id,
                        'user_id' => $user->id,
                    ]);
                });
            });
        });
      
        $u = User::query()->latest()->first();
        factory(Absence::class)->create([
            'user_id' => $u->id,
            'start_at' => now()->subDays(2),
            'end_at' => now()->addDays(1),
        ]);
      
    }
}
