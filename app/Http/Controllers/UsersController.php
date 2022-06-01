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

        return new SuccessResource((object)['data'=>$user]);
    }
    public function getReferral(Request $request){

        $user = Auth::user();
        $referral = User::find($user->id)->referral_code;
        
        return new SuccessResource((object)['data'=>$referral]);

    }
}
