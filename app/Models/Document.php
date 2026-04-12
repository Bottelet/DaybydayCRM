<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasExternalId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasExternalId;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'size', 'path', 'original_filename', 'client_id', 'external_id', 'mime', 'integration_id', 'integration_type', 'source_type', 'source_id'];

    protected static function boot()
    {
        parent::boot();
        // HasExternalId trait handles external_id generation
    }

    //region Relationships

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    //endregion
}
