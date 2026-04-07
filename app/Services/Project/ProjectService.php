<?php

namespace App\Services\Project;

use App\Models\Project;

class ProjectService
{

    private $total=0;
    public function __construct()
    {
        $this->total=Project::count();
    }

    public function getSumOpened()
    {
        $temp= Project::where('status_id', '11')
            ->count();
       if($this->total!=0)
       {
           return ($temp/$this->total)*100;
       }
        return 0;
    }

    public function getSumInprogress()
    {
        $temp= Project::where('status_id', '12')
            ->count();

        if($this->total!=0)
        {
            return ($temp/$this->total)*100;
        }
        return 0;
    }
    public function getSumBlocked()
    {
        $temp= Project::where('status_id', '13')
            ->count();
        if($this->total!=0)
        {
            return ($temp/$this->total)*100;
        }
        return 0;

    }
    public function getSumCanceled()
    {
        $temp= Project::where('status_id', '14')
            ->count();

        if($this->total!=0)
        {
            return ($temp/$this->total)*100;
        }
        return 0;
    }
    public function getSumCompleted()
    {
        $temp= Project::where('status_id', '15')
            ->count();
        if($this->total!=0)
        {
            return ($temp/$this->total)*100;
        }
        return 0;
    }
}