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

    protected $attributes = [
        'is_listed' => false
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

    public function scopeListed($query)
    {
        return $query->where('is_listed', true);
    }

    public function scopeSymbol($query, $symbol)
    {
        return $query->where('symbol','like', "%$symbol%");
    }

    public function scopeSortColumn($query, $column, $direction)
    {
        $priceColumns = ['open_price', 'close_price', 'high_price', 'low_price', 'current_price'];

        if (in_array($column, $priceColumns)) {
            return $query
                ->join('stock_prices as sp', function ($join) {
                    $join->on('stocks.id', '=', 'sp.stock_id')
                        ->whereRaw('sp.id = (
                         SELECT MAX(id) FROM stock_prices
                         WHERE stock_prices.stock_id = stocks.id
                     )');
                })
                ->orderBy("sp.$column", $direction)
                ->select('stocks.*');
        }

        return $query->orderBy($column, $direction);
    }
}
