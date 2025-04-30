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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_listed' => 'boolean',
    ];

    /**
     * Get the sector that this stock belongs to.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Get the prices for this stock.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(StockPrice::class);
    }

    /**
     * Get the latest price for this stock.
     */
    public function latestPrice(): HasOne
    {
        return $this->hasOne(StockPrice::class)->latestOfMany('date');
    }

    /**
     * Get the transactions for this stock.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the watchlists for this stock.
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Get the IPO details for this stock.
     */
    public function ipoDetails(): HasMany
    {
        return $this->hasMany(IpoDetail::class);
    }

    /**
     * Get the holdings for this stock.
     */
    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }

    public function scopeListed($query)
    {
        return $query->where('is_listed', true);
    }

    public function scopeUnlisted($query)
    {
        return $query->where('is_listed', false);
    }

    public function scopeSymbol($query, $symbol)
    {
        return $query->whereRaw('LOWER(symbol) LIKE ?', ['%'.strtolower($symbol).'%']);
    }

    public function scopeSortColumn($query, $column, $direction)
    {
        $priceColumns = ['open_price', 'close_price', 'high_price', 'low_price', 'current_price'];

        if (in_array($column, $priceColumns)) {
            $query->addSelect([
                'sort_value' => StockPrice::select($column)
                    ->whereColumn('stock_prices.stock_id', 'stocks.id')
                    ->latest('date') // or latest('id') if needed
                    ->limit(1),
            ])->orderBy('sort_value', $direction);

            return $query;
        }

        return $query->orderBy($column, $direction);
    }
}
