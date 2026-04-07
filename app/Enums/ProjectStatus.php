<?php

namespace App\Enums;

use App\Models\Status;

class ProjectStatus
{
    public static function open()
    {
        return Status::where(
            ['title'=>'Open', 'source_type'=>'App\Models\Project']
        )->first();
    }
    public static function closed(){
        return Status::where(
            ['title'=>'Closed', 'source_type'=>'App\Models\Project']
        )->first();
    }
    public static function inProgress(){
        return Status::where(
            ['title'=>'In-progress', 'source_type'=>'App\Models\Project']
        )->first();
    }
    public static function blocked(){
        return Status::where(
            ['title'=>'Blocked', 'source_type'=>'App\Models\Project']
        )->first();
    }
    public static function completed(){
        return Status::where(
            ['title'=>'Completed', 'source_type'=>'App\Models\Project']
        )->first();
    }
    public static function cancelled(){
        return Status::where(
            ['title'=>'Cancelled', 'source_type'=>'App\Models\Project']
        )->first();
    }
}