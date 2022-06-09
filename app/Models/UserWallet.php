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
        'amount',
        'trans_kind',
        'token_type',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
