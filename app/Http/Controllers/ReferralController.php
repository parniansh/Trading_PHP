<?php

namespace App\Http\Controllers;

use App\Http\Resources\ErrorResource;
use App\Models\Referral;
use Exception;
use Illuminate\Http\Request;


class ReferralController extends Controller
{
   //first generate the referral code

    public function generateReferralCode(){
        $referralCode = "generalReferralCode";
        return $referralCode;
    }

   public function asignReferralCode(Request $request){
       $refferalCode = generateReferralCode();
      $requestParentRefferal = $request->parentReferralCode;
       if(!$requestParentRefferal){
        $parentId = null;
        $categorySerial = strval($request->user->id);
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
        $categorySerial = strval($parentReferral_DB->category_serial/$request->user->id);
        $parentReferralCode = $parentReferral_DB->referral_code;

       }
       

        

       return  $asignedReferralCode = Referral::create(['user_id'=> $request->user->id, 'referral_code'=> $refferalCode, 'parent_id'=>$parentId ,
        'category_serial'=> $categorySerial, 'parent_referral_code'=> $parentReferralCode]);

   }
}

