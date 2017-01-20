<?php

return [

    'notifications' => [
        'task' => [
            'created' => ':title was created by :creator, and assigned to you',
            'status' => ':title was completed by :username',
            'time' =>  ':username inserted a new time for :title',
            'assign' => ':username assigned a task to you',
        ],
        'lead' => [
            'created' => ':title was created by :creator and assigned to you',
            'status' => ':title was completed by :username',
            'deadline' => ':username updated the deadline for this :title',
            'assign' => ':username assigned a lead to you',
        ],
        'client' => [
            'created' => 'Client :company was assigned to you',
            'assign' => ':username assigned :company to you',
        ]
    ],
    'log' => [
        'task' => [
            'created' => ':title was created by :creator and assigned to :assignee',
            'status' => 'Tasl was completed by :username',
            'time' =>  ':username inserted a new time for this task',
            'assign' => ':username assigned task to :assignee',
        ],
        'lead' => [
            'created' => ':title was created by :creator and assigned to :assignee',
            'status' => 'Lead was completed by :username',
            'deadline' => ':username updated the deadline for this lead',
            'assign' => ':username assigned lead to :assignee',
        ],
        'client' => [
            'created' => 'Client :company was assigned to :assignee',
            'assign' => ':username assigned client to :assignee',
        ]
    ]
];
