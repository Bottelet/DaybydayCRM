<?php
namespace App\Models;

use App\Observers\ElasticSearchObserver;
use App\Services\Comment\Commentable;
use App\Traits\DeadlineTrait;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property string title
 * @property string external_id
 * @property integer user_assigned_id
 * @property Status status
 * @property Client client
 * @property integer invoice_id
 * @property integer status_id
 * @property Invoice invoice
 */
class Lead extends Model implements Commentable
{
    use SearchableTrait, SoftDeletes, DeadlineTrait;

    protected $searchableFields = ['title'];

    const LEAD_STATUS_CLOSED = "closed";

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
    protected $dates = ['deadline'];

    protected $hidden = ['remember_token'];


    public static function boot()
    {
        parent::boot();

        // This makes it easy to toggle the search feature flag
        // on and off. This is going to prove useful later on
        // when deploy the new search engine to a live app.
        //if (config('services.search.enabled')) {
        static::observe(ElasticSearchObserver::class);
        //}
    }

    public function getRouteKeyName()
    {
        return 'external_id';
    }

    public function displayValue()
    {
        return $this->title;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_assigned_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_created_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'source');
    }

    public function getCreateCommentEndpoint(): String
    {
        return route('comments.create', ['type' => 'lead', 'external_id' => $this->external_id]);
    }

    public function getShowRoute()
    {
        return route('leads.show', [$this->external_id]);
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'source');
    }
    
    public function appointments()
    {
        return $this->morphMany(Appointment::class, 'source');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_assigned_id);
    }

    public function isClosed()
    {
        return $this->status == self::LEAD_STATUS_CLOSED;
    }
    
    public function invoice()
    {
        return $this->morphMany(Invoice::class, 'source');
    }
    /**
     * @return array
     */
    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }

    public function offers()
    {
        return $this->morphMany(Offer::class, 'source');
    }

    public function convertToOrder()
    {
        if(!$this->canConvertToOrder()) {
            return false;
        }
        $invoice = Invoice::create([
            'status' => 'draft',
            'client_id' => $this->client->id,
            'external_id' =>  Uuid::uuid4()->toString()
        ]);

        $this->invoice_id = $invoice->id;
        $this->status_id = Status::typeOfLead()->where('title', 'Closed')->first()->id;
        $this->save();

        return $invoice;
    }

    public function canConvertToOrder()
    {
        if($this->invoice) {
            return false;
        }
        return true;
    }
}
