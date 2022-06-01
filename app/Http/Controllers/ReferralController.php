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

   public function asignReferralCode($obj){
       $referralCode = ReferralController::generateReferralCode();
      $requestParentRefferal = $obj->parentReferralCode;
       if(!$requestParentRefferal){
        $parentId = null;
        $categorySerial = strval($obj->userId);
        $parentReferralCode = null;
       }else{
        $parentReferral_DB = Referral::where(["referral_code"=> $requestParentRefferal])->first();
        if(!$parentReferral_DB){
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Referral Is Invalid'),
            ]);
        }
        $parentId =$parentReferral_DB->user_id ;
        $categorySerial = $parentReferral_DB->category_serial.'/'.$obj->userId;
        $parentReferralCode = $parentReferral_DB->referral_code;

       }
       

        

       return  $asignedReferralCode = Referral::create(['user_id'=> $obj->userId, 'referral_code'=> $referralCode, 'parent_id'=>$parentId ,
        'category_serial'=> $categorySerial, 'parent_referral_code'=> $parentReferralCode]);

   }
}

