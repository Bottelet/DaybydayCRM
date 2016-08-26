<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['name', 'size', 'path', 'file_display', 'fk_client_id'];

    public function clients()
    {
        $this->belongsTo(Client::class, 'fk_client_id');
    }
}
