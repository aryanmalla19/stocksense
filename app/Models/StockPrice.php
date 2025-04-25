<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPrice extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'stock_id',
        'open_price',
        'close_price',
        'high_price',
        'low_price',
        'current_price',
        'volume',
        'date',
    ];

    protected $casts = [
        'open_price' => 'decimal:2',
        'close_price' => 'decimal:2',
        'high_price' => 'decimal:2',
        'low_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'volume' => 'integer',
        'date' => 'datetime',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
