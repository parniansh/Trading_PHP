<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    public $timestamps = false;

    

    protected $fillable = [
        'user_id',
        'referral_code',
        'parent_id',
        'parent_referral_code',
        'category_serial',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
