<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false; // Disable timestamps


    /**
     * Get the stocks belonging to this sector.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
