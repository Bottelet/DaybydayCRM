<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_number',
        'invoice_number',
        'company',
        'country',
        'currency',
        'vat',
        'language',
        'max_users',
        'start_time',
        'end_time',
    ];

    public static function cached(): ?self
    {
        return Cache::remember('app_settings', 3600, fn () => static::first());
    }

    public static function clearCache(): void
    {
        Cache::forget('app_settings');
    }

    # region Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    # endregion
}
