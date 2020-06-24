<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;

class Status extends Model
{
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function scopeTypeOfTask(Builder $query)
    {
        return $query->where('source_type', Task::class);
    }

    public function scopeTypeOfLead(Builder $query)
    {
        return $query->where('source_type', Lead::class);
    }

    public function scopeTypeOfProject(Builder $query)
    {
        return $query->where('source_type', Project::class);
    }
}
