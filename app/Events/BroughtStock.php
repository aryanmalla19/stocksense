<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroughtStock
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Transaction $transaction,
        public $user
    ) {}
}
