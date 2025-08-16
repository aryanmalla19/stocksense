<?php

namespace App\Models;

use App\Enums\IpoApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpoApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'ipo_id',
        'applied_shares',
        'status',
        'applied_date',
        'allotted_shares',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'applied_shares' => 'integer',
        'allotted_shares' => 'integer',
        'applied_date' => 'datetime',
        'status' => IpoApplicationStatus::class,
    ];

    protected $attributes = [
        'status' => 'pending',
        'applied_date' => null, // Laravel can use mutator or handle in controller
        'allotted_shares' => 0,
    ];

    protected static function booted()
    {
        static::creating(function ($ipoApplication) {
            if (is_null($ipoApplication->applied_date)) {
                $ipoApplication->applied_date = now();
            }
        });
    }

    /**
     * Get the user who submitted this IPO application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the IPO details for this application.
     */
    public function ipo(): BelongsTo
    {
        return $this->belongsTo(IpoDetail::class, 'ipo_id');
    }

    public function scopeIsAllotted($query)
    {
        return $query
            ->where('status', 'allotted')
            ->whereNotNull('allotted_shares')
            ->where('allotted_shares', '>', 0);
    }
}
