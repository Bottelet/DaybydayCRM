<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class StatusTableSeeder extends Seeder
{
    private array $statuses = [
        // Tasks
        [Task::class,    'Open',           '#2FA599'],
        [Task::class,    'In-progress',    '#2FA55E'],
        [Task::class,    'Pending',        '#EFAC57'],
        [Task::class,    'Waiting client', '#60C0DC'],
        [Task::class,    'Blocked',        '#E6733E'],
        [Task::class,    'Closed',         '#D75453'],
        // Leads
        [Lead::class,    'Open',           '#2FA599'],
        [Lead::class,    'Pending',        '#EFAC57'],
        [Lead::class,    'Waiting client', '#60C0DC'],
        [Lead::class,    'Closed',         '#D75453'],
        // Projects
        [Project::class, 'Open',           '#2FA599'],
        [Project::class, 'In-progress',    '#3CA3BA'],
        [Project::class, 'Blocked',        '#60C0DC'],
        [Project::class, 'Cancelled',      '#821414'],
        [Project::class, 'Completed',      '#D75453'],
    ];

    public function run(): void
    {
        foreach ($this->statuses as [$type, $title, $color]) {
            Status::query()->firstOrCreate(
                ['source_type' => $type, 'title' => $title],
                ['external_id' => Uuid::uuid4()->toString(), 'color' => $color]
            );
        }
    }
}
