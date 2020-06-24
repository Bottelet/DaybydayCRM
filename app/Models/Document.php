<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use  SoftDeletes;

    protected $fillable = ['name', 'size', 'path', 'original_filename', 'client_id', 'external_id', 'mime', 'integration_id', 'integration_type', 'source_type', 'source_id'];

    public function clients()
    {
        $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the owning imageable model.
     */
    public function sourceable()
    {
        return $this->morphTo();
    }
}
