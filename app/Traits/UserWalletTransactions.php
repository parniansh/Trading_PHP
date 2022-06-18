<?php

namespace App\Traits;

use App\Http\Requests\UserWalletRequest;
use App\Http\Requests\UserWalletTransactionRequest;
use App\Models\User;
use App\Models\UserWalletTransactions as ModelsUserWalletTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait UserWalletTransactions
{


    public function add(UserWalletTransactionRequest $request){
        $user = Auth::user();
        $userWalletTrans = ModelsUserWalletTransactions::Create([
            "amount" => $request->amount,
            "trans_kind" => $request->transKind,
            "user_id" => $user->id,
            "token_type" => $request->tokenType,
            ]);
            return $userWalletTrans;
    }

    public function getList(int $id){

        return User::find($id)->userWalletTransaction->take(100);
        
    }

    public function getById(int $id){
       
        return ModelsUserWalletTransactions::find($id)->first();
       


    }
}