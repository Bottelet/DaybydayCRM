<?php

use App\Models\Project;
use Illuminate\Database\Seeder;
use App\Models\Status;
use App\Models\Task;
use App\Models\Lead;
use Ramsey\Uuid\Uuid;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Open';
        $status->source_type = Task::class;
        $status->color = '#2FA599';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'In-progress';
        $status->source_type = Task::class;
        $status->color = '#2FA55E';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Pending';
        $status->source_type = Task::class;
        $status->color = '#EFAC57';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Waiting client';
        $status->source_type = Task::class;
        $status->color = '#60C0DC';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Blocked';
        $status->source_type = Task::class;
        $status->color = '#E6733E';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Closed';
        $status->source_type = Task::class;
        $status->color = '#D75453';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Open';
        $status->source_type = Lead::class;
        $status->color = '#2FA599';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Pending';
        $status->source_type = Lead::class;
        $status->color = '#EFAC57';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Waiting client';
        $status->source_type = Lead::class;
        $status->color = '#60C0DC';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Closed';
        $status->source_type = Lead::class;
        $status->color = '#D75453';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Open';
        $status->source_type = Project::class;
        $status->color = '#2FA599';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'In-progress';
        $status->source_type = Project::class;
        $status->color = '#3CA3BA';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Blocked';
        $status->source_type = Project::class;
        $status->color = '#60C0DC';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Cancelled';
        $status->source_type = Project::class;
        $status->color = '#821414';
        $status->save();

        $status = new Status;
        $status->external_id = Uuid::uuid4();
        $status->title = 'Completed';
        $status->source_type = Project::class;
        $status->color = '#D75453';
        $status->save();
    }
}
