<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpoDetail extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'stock_id',
        'issue_price',
        'total_shares',
        'open_date',
        'close_date',
    ];
}
