<?php

namespace App\Traits;

use App\Http\Requests\UserWalletTransactionRequest;
use App\Models\User;
use App\Models\UserWalletTransactions as ModelsUserWalletTransactions;
use Illuminate\Support\Facades\Auth;

trait UserWalletTransactions
{


    public function add(UserWalletTransactionRequest $request){
        $userWalletTrans = ModelsUserWalletTransactions::Create([
            "amount" => $request->amount,
            "trans_kind" => $request->trans_kind,
            "user_id" => $request->user_id,
            "token_type" => $request->token_type,
            "execution_id"=>$request->execution_id
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