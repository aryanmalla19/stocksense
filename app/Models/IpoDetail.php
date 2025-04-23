<?php

namespace App\Models;

use App\Enums\IpoDetailStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpoDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'issue_price',
        'total_shares',
        'open_date',
        'close_date',
        'listing_date',
        'ipo_status',
    ];

    protected $casts = [
        'issue_price' => 'decimal:2',
        'total_shares' => 'integer',
        'open_date' => 'datetime',
        'close_date' => 'datetime',
        'listing_date' => 'datetime',
        'ipo_status' => IpoDetailStatus::class,
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IpoApplication::class, 'ipo_id');
    }

    public function getIpoStatusAttribute(): string
    {
        $now = now();

        if ($this->listing_date && $now->gte($this->listing_date)) {
            return IpoDetailStatus::Allotted->value;
        }

        if ($this->open_date && $now->lt($this->open_date)) {
            return IpoDetailStatus::Upcoming->value;
        }

        if ($this->open_date && $this->close_date && $now->between($this->open_date, $this->close_date)) {
            return IpoDetailStatus::Opened->value;
        }

        if ($this->close_date && $now->gt($this->close_date)) {
            return IpoDetailStatus::Closed->value;
        }

        return 'unknown';
    }

    public function scopeStock($query, $id)
    {
        return $query->where('stock_id', $id);
    }

}
