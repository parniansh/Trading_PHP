<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;
    public $timestamps = true;


    protected $fillable = [
        'user_id',
        'rial_balance',
        'mazin_balance'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
