<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpoDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'stock_id',
        'issue_price',
        'total_shares',
        'open_date',
        'close_date',
        'listing_date',
        'ipo_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_price' => 'decimal:2',
        'total_shares' => 'integer',
        'open_date' => 'datetime',
        'close_date' => 'datetime',
        'listing_date' => 'datetime',
        'ipo_status' => 'string',
    ];

    /**
     * Get the stock associated with this IPO.
     *
     * @return BelongsTo
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the applications for this IPO.
     *
     * @return HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(IpoApplication::class, 'ipo_id');
    }
}