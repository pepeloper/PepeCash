<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Spending extends Model
{

    protected $fillable = ['name', 'amount', 'created_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getAmountFormattedAttribute()
    {
        return number_format($this->amount / 100, 2);
    }
}
