<?php

namespace App\Enums;

use App\Models\Status;

class LeadStatus
{
    public static function open()
    {
        return Status::where(
            ['title'=>'Open', 'source_type'=>'App\Models\Lead']
        )->first();
    }
    public static function pending()
    {
        return Status::where(
            ['title'=>'Pending', 'source_type'=>'App\Models\Lead']
        )->first();
    }

    public static function waitingclient()
    {
        return Status::where(
            ['title'=>'Waiting client', 'source_type'=>'App\Models\Lead']
        )->first();
    }
    public static function closed()
    {
        return Status::where(
            ['title'=>'Closed', 'source_type'=>'App\Models\Lead']
        )->first();
    }

    

}