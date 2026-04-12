<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Comment\Commentable;
use App\Traits\DeadlineTrait;
use App\Traits\HasExternalId;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model implements Commentable
{
    use DeadlineTrait;
    use HasExternalId;
    use HasFactory;
    use SearchableTrait;
    use SoftDeletes;

    public const PROJECT_STATUS_CLOSED = 'Closed';

    protected $searchableFields = ['title'];

    protected $fillable = [
        'title',
        'description',
        'external_id',
        'status_id',
        'user_assigned_id',
        'user_created_id',
        'client_id',
        'lead_id',
        'deadline',
        'invoice_id',
    ];

    protected $casts = [
        'deadline' => 'date',
        'deleted_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        // HasExternalId trait handles external_id generation
    }

    // getRouteKeyName() is provided by HasExternalId trait

    public function displayValue()
    {
        return $this->title;
    }

    # region Relationships

    public function activity()
    {
        return $this->morphMany(Activity::class, 'source');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'source');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'source');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    # endregion

    public function isClosed()
    {
        // Check if status relationship exists and compare title
        return $this->status && $this->status->title == self::PROJECT_STATUS_CLOSED;
    }

    public function getCreateCommentEndpoint(): string
    {
        return route('comments.create', ['type' => 'project', 'external_id' => $this->external_id]);
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }
}
