<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Stock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'symbol',
        'company_name',
        'sector_id',
        'description',
    ];

    /**
     * Get the sector this stock belongs to.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);

    }

    public function prices(): HasMany
    {
        return $this->hasMany(StockPrice::class, 'stock_id');
    }

    public function latestPrice(): HasOne
    {
        return $this->hasOne(StockPrice::class)->latest('date');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function ipoDetails(): HasMany
    {
        return $this->hasMany(IpoDetail::class, 'stock_id');
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }
}
