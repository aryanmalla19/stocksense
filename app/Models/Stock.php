<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     *
     * @return BelongsTo
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);

    }



    public function prices(): HasMany
    {
        return $this->hasMany(StockPrice::class);
     
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
        return $this->hasMany(IpoDetail::class);
    }

    public function holdings(): HasMany
    {
       return $this->hasMany(Holding::class);
    }


    
}