<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\User;



class UsersController extends Controller
{
    //
    public function get(Request $request){
        $user = Auth::user();
        $user->referral_code = $this->getReferral($user->id);
        return new SuccessResource((object)['data'=>$user]);
    }
    public function getReferral($id){
        $referral = User::find($id)->referral_code;
        return $referral;
    }
}
