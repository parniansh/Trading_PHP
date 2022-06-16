<?php

namespace App\Traits;

use App\Http\Resources\ErrorResource;
use App\Models\UserWallet as ModelsUserWallet;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait UserWallet{


    public function add(Request $request){
        //fekr konam auth ro bayad bardaram   ???
        $user = Auth::user();
        $userwallet = ModelsUserWallet::where(["user_id"=> $user->id]);
        if(!$userwallet){
            $userWallet = ModelsUserWallet::Create([
                "user_id" => $user->id,
                "rial_balance" => $request->rialBalance,
                "mazin_balance" => $request->mazinBalance,
                ]);
                return $userWallet;
        }else{
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Wallet Already Exists'),
            ]);        }
        
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