<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserWalletRequest;
use App\Models\UserWallet;
use GuzzleHttp\Promise\Create;
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
    }
    
    public function getList(Request $request){

        $walletList = auth()->user()->userWallet->latest()->take(100);
        return $walletList;
    }

    public function get(Request $request){
        $user = Auth::user();
        $walletRecord = auth()->user()->userWallet->find($request->userWalletId);
        return $walletRecord;

    }
}
