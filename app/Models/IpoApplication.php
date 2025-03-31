<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpoApplication extends Model
{
    use HasFactory;

    protected $fillable =[
        'user_id',
        'ipo_id',
        'applied_shares',
        'status',
    ];

}
