<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Comment\Commentable;
use App\Traits\DeadlineTrait;
use App\Traits\HasExternalId;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property string title
 * @property string external_id
 * @property int user_assigned_id
 * @property Status status
 * @property Client client
 * @property int invoice_id
 * @property int status_id
 * @property Invoice invoice
 */
class Lead extends Model implements Commentable
{
    use DeadlineTrait;
    use HasExternalId;
    use HasFactory;
    use SearchableTrait;
    use SoftDeletes;

    protected $searchableFields = ['title'];

    /**
     * @deprecated Use LeadStatus::CLOSED->value instead
     */
    public const LEAD_STATUS_CLOSED = 'closed';

    protected $fillable = [
        'external_id',
        'title',
        'description',
        'status_id',
        'user_assigned_id',
        'user_created_id',
        'client_id',
        'result',
        'deadline',
        'invoice_id',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = ['remember_token'];

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

    // region Relationships

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
        return $this->morphMany(Invoice::class, 'source');
    }

    public function notes()
    {
        return $this->comments();
    }

    public function offers()
    {
        return $this->morphMany(Offer::class, 'source');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'lead_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    // endregion

    public function getCreateCommentEndpoint(): string
    {
        return route('comments.create', ['type' => 'lead', 'external_id' => $this->external_id]);
    }

    public function getShowRoute()
    {
        return route('leads.show', [$this->external_id]);
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_assigned_id);
    }

    public function isClosed()
    {
        // Check if status relationship exists and compare title
        return $this->status && LeadStatus::isClosed($this->status->title);
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }

    public function convertToOrder()
    {
        if (! $this->canConvertToOrder()) {
            return false;
        }
        $invoice = Invoice::create([
            'status' => 'draft',
            'client_id' => $this->client->id,
            'external_id' => Uuid::uuid4()->toString(),
        ]);

        $this->invoice_id = $invoice->id;
        $this->status_id = Status::typeOfLead()->where('title', 'Closed')->first()->id;
        $this->save();

        return $invoice;
    }

    public function canConvertToOrder()
    {
        if ($this->invoice) {
            return false;
        }

        return true;
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
}
