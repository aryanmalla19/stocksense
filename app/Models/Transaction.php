<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'stock_id', 'type', 'quantity', 'price', 'timestamp'];
}
