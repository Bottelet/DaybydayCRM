<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Activity extends Model
{
    use HasExternalId;
    use HasFactory;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $fillable = [
        'causer_id',
        'causer_type',
        'text',
        'source_type',
        'source_id',
        'properties',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'properties' => 'collection',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($activity) {
            // HasExternalId trait handles external_id generation

            if (empty($activity->ip_address)) {
                $activity->ip_address = request()->ip() ?: '127.0.0.1';
            }
        });
    }

    // region Relationships

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // endregion

    public function getExtraProperty(string $propertyName)
    {
        return Arr::get($this->properties->toArray(), $propertyName);
    }

    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, Model $source): Builder
    {
        return $query
            ->where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey());
    }
}
