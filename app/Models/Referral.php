<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    
    

    protected $fillable = [
        'refferal_code',
        'parent_id',
        'parent_referral_code',
        'categorySerial',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
