<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class UsersController extends Controller
{
    //
    public function get(Request $request){
        $user = Auth::user();
        return new SuccessResource((object)['data'=>$user]);
    }
}
