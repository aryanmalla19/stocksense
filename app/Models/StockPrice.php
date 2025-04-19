<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPrice extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'stock_id',
        'open_price',
        'close_price',
        'high_price',
        'low_price',
        'current_price',
        'volume',
        'date',
        'current_price', // Add this
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'open_price' => 'decimal:2',
        'close_price' => 'decimal:2',
        'high_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'low_price' => 'decimal:2',
        'volume' => 'integer',
        'date' => 'datetime',
        'current_price' => 'decimal:2', // Add this for consistent formatting
    ];

    /**
     * Get the stock this price belongs to.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}