<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class showInterest extends Model
{
    protected $fillable = ['user_id', 'borrow_id', 'currency_id', 'amount', 'rate', 'interest', 'start_date', 'end_date', 'days'];
}
