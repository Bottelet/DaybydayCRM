<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    // region Relationships

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

    // endregion

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

    /**
     * Check if a status ID is valid for a specific type
     *
     * @param int $statusId
     * @param string $sourceType The fully qualified class name (e.g., Task::class, Lead::class, Project::class)
     * @return bool
     */
    public static function isValidForType(int $statusId, string $sourceType): bool
    {
        return self::where('id', $statusId)
            ->where('source_type', $sourceType)
            ->exists();
    }
}
