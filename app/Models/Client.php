<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Events\ClientAction;
use App\Http\Controllers\ClientsController;
use App\Traits\HasExternalId;
use App\Traits\SearchableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed user_id
 * @property mixed company_name
 * @property mixed vat
 * @property mixed id
 */
class Client extends Model
{
    use HasExternalId;
    use HasFactory;
    use SearchableTrait;
    use SoftDeletes;

    protected $searchableFields = ['company_name', 'vat', 'address'];

    protected $fillable = [
        'external_id',
        'name',
        'company_name',
        'vat',
        'email',
        'address',
        'zipcode',
        'city',
        'primary_number',
        'secondary_number',
        'industry_id',
        'company_type',
        'user_id',
        'client_number'];

    public static function boot()
    {
        parent::boot();
    }

    public function updateAssignee(User $user)
    {
        $this->user_id = $user->id;
        $this->save();

        event(new ClientAction($this, ClientsController::UPDATED_ASSIGN));
    }

    public function displayValue()
    {
        return $this->company_name;
    }

    //region Relationships

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'source');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->whereIsPrimary(true);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'client_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //endregion

    public function getPrimaryContactAttribute()
    {
        return $this->hasMany(Contact::class)->whereIsPrimary(true)->first();
    }

    public function getAssignedUserAttribute()
    {
        return User::findOrFail($this->user_id);
    }

    public static function whereExternalId($external_id)
    {
        return self::where('external_id', $external_id)->first();
    }

    public function getSearchableFields(): array
    {
        return $this->searchableFields;
    }
}
