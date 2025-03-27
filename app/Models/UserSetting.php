<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory; // ✅ Required for factory()
    protected $fillable = [
        'user_id',
        'notification_enabled',
        'currency'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
