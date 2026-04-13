<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Services\Comment\Commentable;
use App\Traits\DeadlineTrait;
use App\Traits\HasExternalId;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed external_id
 */
class Task extends Model implements Commentable
{
    use DeadlineTrait;
    use HasExternalId;
    use HasFactory;
    use SearchableTrait;
    use SoftDeletes;

    /**
     * @deprecated Use TaskStatus::CLOSED->value instead
     */
    public const TASK_STATUS_CLOSED = 'closed';

    protected $searchableFields = ['title'];

    protected $fillable = [
        'title',
        'description',
        'external_id',
        'status_id',
        'user_assigned_id',
        'user_created_id',
        'client_id',
        'deadline',
        'project_id',
        'invoice_id',
    ];

    protected $casts = [
        'deadline'   => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = ['remember_token'];

    public static function boot()
    {
        parent::boot();
        // HasExternalId trait handles external_id generation
    }

    /**
     * Find a model by external_id (UUID).
     *
     * @return static|null
     */
    public static function findByExternalId(string $externalId)
    {
        return static::where('external_id', $externalId)->first();
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

    public function appointments()
    {
        return $this->morphMany(Appointment::class, 'source');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    # endregion

    public function getCreateCommentEndpoint(): string
    {
        return route('comments.create', ['type' => 'task', 'external_id' => $this->external_id]);
    }

    public function getShowRoute()
    {
        return route('tasks.show', [$this->external_id]);
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_assigned_id);
    }

    public function getCreatorUserAttribute()
    {
        return User::findOrFail($this->user_created_id);
    }

    public function canUpdateInvoice()
    {
        // If there is no invoice, it should be possible, because it also creates
        if ( ! $this->invoice) {
            return true;
        }

        return $this->invoice->canUpdateInvoice();
    }

    public function isClosed()
    {
        // Check if status relationship exists and compare title
        return $this->status && TaskStatus::isClosed($this->status->title);
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }
}
