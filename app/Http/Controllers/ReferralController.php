<?php

namespace App\Http\Controllers;

use App\Http\Resources\ErrorResource;
use App\Models\Referral;
use Exception;
use Illuminate\Http\Request;


class ReferralController extends Controller
{
   //first generate the referral code

    private function generateReferralCode(){
        $referralCode = "generalReferralCode";
        return $referralCode;
    }

   public function asignReferralCode($parentReferralCode, $userId){
    // $userId = $request->userId;
    // $parentReferralCode = $request->parentReferralCode;
       $referralCode = $this->generateReferralCode();
       if(!$parentReferralCode){
        $parentId = null;
        $categorySerial = strval($userId);
       }else{
        $parentReferral_DB = Referral::where(["referral_code"=> $parentReferralCode])->first();
        if(!$parentReferral_DB){
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Referral Is Invalid'),
            ]);
        }
        $parentId =$parentReferral_DB->user_id ;
        $categorySerial = $parentReferral_DB->category_serial.'/'.$userId;

       }
      $referral = Referral::create(['user_id'=> $userId, 'referral_code'=> $referralCode, 'parent_id'=>$parentId ,
       'category_serial'=> $categorySerial]);

       return  $referral;
   }
}

