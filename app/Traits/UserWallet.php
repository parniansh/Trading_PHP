<?php

namespace App\Traits;

use App\Models\UserWallet as ModelsUserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait UserWallet{


    public function add(Request $request){
        //fekr konam auth ro bayad bardaram   ???
        $user = Auth::user();
        $userWallet = ModelsUserWallet::Create([
            "user_id" => $user->id,
            "rial_balance" => $request->rialBalance,
            "mazin_balance" => $request->mazinBalance,
            ]);
            return $userWallet;
    }

    public function update(Request $request){
        $userWallet = ModelsUserWallet::where(["user_id"=> $request->userId])->first();
        $userWallet->update(['rial_balance'=> $request->rialBalance,'mazin_balance'=> $request->mazinBalance]);
      //  $usercode->update(['code'=>$password,'expired'=>0,'expire_date'=>$expire_date]);
      return $userWallet;
    }


    public function getById(Request $request){
       
        $user = Auth::user();
       $walletRecord = $user->userWallet->find($request->userWalletId);
       return $walletRecord;


    }
}