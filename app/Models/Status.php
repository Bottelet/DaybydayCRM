<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    //region Relationships

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    //endregion

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
