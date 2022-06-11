<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserWalletRequest;
use App\Http\Resources\ErrorResource;
use App\Models\UserWallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWalletController extends Controller
{
    //


    public function add(UserWalletRequest $request){
        $user = Auth::user();
        $userWallet = UserWallet::Create([
            "amount" => $request->amount,
            "trans_kind" => $request->transKind,
            "user_id" => $user->id,
            "token_type" => $request->tokenType,
            ]);
            return $userWallet;
    }
    
    
    public function getList(Request $request){

        $walletList = auth()->user()->userWallet->take(100);
        return $walletList;
    }

    public function getById(Request $request){
       
        $user = Auth::user();
       $walletRecord = $user->userWallet->find($request->userWalletId);
       return $walletRecord;


    }
}
