<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrow extends Model
{
    protected $fillable = ['amount', 'rate', 'borrowed_date', 'remaining_amount', 'orginal_amount', 'interest_date', 'currency_id', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function interests()
    {
        return $this->hasMany(Interest::class);
    }
    public function repayments()
    {
        return $this->hasMany(repayment::class);
    }
    public function showInterest()
    {
        return $this->hasOne(showInterest::class);
    }
}
