<?php

namespace App\Traits;

use App\Http\Resources\ErrorResource;
use App\Models\User;
use App\Models\UserWallet;
use Error;

trait UserWallets{


    public function add(float $rialBalance , float $mazinBalance, int $userid ){

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

    public function update(object $array){
        $userWallet = UserWallet::where(["user_id"=> $array->userId])->first();
        return $userWallet->update((array)$array);
       
    }


    public function getById(int $id){
        return UserWallet::find($id)->first;
    }


    public function getByUser(int $id){
       
        return User::find($id)->userWallet;
        


    }
}