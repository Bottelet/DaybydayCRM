<?php

return [

    'notifications' => [
        'task' => [
            'created' => ':title was created by :creator, and assigned to you',
            'status' => ':title was completed by :username',
            'time' =>  ':username inserted a new time for :title',
            'assign' => ':username assigned a task to you',
        ],
    ],
    'log' => [
        'task' => [
            'created' => ':title was created by :creator and assigned to :assignee',
            'status' => 'Tasl was completed by :username',
            'time' =>  ':username inserted a new time for this task',
            'assign' => ':username assigned task to :assignee',
        ]
    ]
];
