<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class repayment extends Model
{
    protected $fillable = ['user_id', 'borrow_id', 'currency_id', 'amount', 'pay_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
