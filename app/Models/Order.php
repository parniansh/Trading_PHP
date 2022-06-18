<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    public $timestamps = true;


    protected $fillable = [
        'user_id',
        'amount',
        'unit_price',
        'order_type',
        'balance',
        'state'
        
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderExecution(){
        return $this->hasMany(orderExecution::class);
    }
}
