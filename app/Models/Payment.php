<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    public $timestamps = true;


    protected $fillable = [
        'user_id',
        'amount',
        'paymant_type',
        'state'
        
        
    ];
    public function paymentExecution(){
        return $this->hasOne(PaymentExecution::class);
    }
}
