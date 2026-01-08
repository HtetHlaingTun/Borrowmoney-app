<?php

namespace App\Models;

use App\Models\Borrow;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $fillable = ['int_pay_date', 'amount', 'user_id', 'borrow_id', 'start_date', 'end_date'];

    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }
}
