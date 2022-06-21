<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentExecution extends Model
{
    use HasFactory;
    public $timestamps = true;


    protected $fillable = [
        'user_id',
        'amount',
        'paymant_type',   
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
