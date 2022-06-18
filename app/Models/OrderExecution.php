<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderExecution extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable= [
        'order_Id',
        'amount',
        'price'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
