<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['name', 'size', 'path', 'file_display', 'fk_client_id'];

    public function clients()
    {
        $this->belingsTo('clients', 'fk_client_id');
    }
}
