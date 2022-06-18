<?php

namespace App\Traits;

use App\Http\Resources\ErrorResource;
use App\Models\User;
use App\Models\UserWallet;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait UserWallets{


    public function add(float $rialBalance , float $mazinBalance, int $userid ){
        //fekr konam auth ro bayad bardaram   ???
        $userwallet = UserWallet::where(['user_id'=> $userid])->first();
        if(!$userwallet){
            $userWallet = UserWallet::Create([
                "user_id" => $userid,
                "rial_balance" => $rialBalance,
                "mazin_balance" => $mazinBalance,
                ]);
                return $userWallet;
        }else{
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Wallet Already Exists'),
            ]);        }
        
    }

    public function update(Request $request){
        $userWallet = UserWallet::where(["user_id"=> $request->userId])->first();
        $userWallet->update(['rial_balance'=> $request->rialBalance,'mazin_balance'=> $request->mazinBalance]);
      //  $usercode->update(['code'=>$password,'expired'=>0,'expire_date'=>$expire_date]);
      return $userWallet;
    }


    public function getById(int $id){
       
        $walletRecord = User::find($id)->userWallet;
       return $walletRecord;


    }
}