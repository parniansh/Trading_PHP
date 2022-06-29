<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWalletTransactions extends Model
{
    use HasFactory;
    public $timestamps = true;


    protected $fillable = [
        'user_id',
        'amount',
        'trans_kind',
        'token_type',
        'execution_id',
        'payment_id'
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
