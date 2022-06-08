<?php

namespace App\Http\Controllers;

use App\Helpers\SerializeValidationErrorResponseHelper;
use App\Http\Requests\OtpCodeRequest;
use App\Http\Requests\OtpLoginRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserCodes;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Kavenegar;
use App\Http\Controllers\ReferralController;
use App\Models\Referral;

class LoginController extends Controller
{
    //
    private $phoneNumber; 
    public function createUser(Request $request, string $password){
    
        $validation = Validator::make(['name'=>$request->name],[
            'name' => 'required'
        ]);
        if($validation->fails()){
            return new ErrorResource((object)[
                'error' => __('validation.RequestValidation'),
                'message' => (new SerializeValidationErrorResponseHelper((object)$validation->errors()))->result,
            ]);
        }
    $user = User::create(["phone"=> $request -> phone,'name'=>$request->name, "password"=>$password]);

    $ref = $this->asignReferralCode($request->parentReferralCode, $user->id);

    if(is_object($ref)){
        return $ref;
    }
    }


    public function asignReferralCode($parentReferralCode, $userId){
        // $userId = $request->userId;
        // $parentReferralCode = $request->parentReferralCode;
           $referralCode = uniqid();
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


    public function otpCodeRequest(OtpCodeRequest $request){
        
        $this->phoneNumber = $request->phone;
        $user = User::where('phone', $request -> phone) ->first();
        $token = rand(100000,999999);
        $now = strtotime(date('Y-m-d H:i:s'));
        $expire_date = date('Y-m-d H:i:s',$now+(env('OTP_EXPIRE_MINUTES')*60));
        $password = Hash::make($token);
        if(!$user){          
            $this->createUser($request,$password);
           // app(ReferralController::class)->asignReferralCode($request->parentReferralCode, $user->id);
        }else{
            $user = User::where("phone",$request -> phone)->update(["password"=>$password]);
        }
        $user = User::where('phone',$request->phone)->first();
        $usercode = UserCodes::where("user-id",$user->id)->first();
        if(!$usercode){
            $usercode = UserCodes::create(["code" => $password,"user-id"=>$user->id,'expired'=>0,'expire_date'=>$expire_date]);
        }else{
            $usercode->update(['code'=>$password,'expired'=>0,'expire_date'=>$expire_date]);
        }
        try{
            $sender = env("OTP_SENDER");		   
            $receptor = strval($user -> phone);		
            $template = "login";
            $result = Kavenegar::VerifyLookup($receptor, $token, $token2=null, $token3=null, $template, $type = null);
            return new SuccessResource((object)['data'=>(object)$result[0]]);

        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            return new ErrorResource((object)[
                'error' => __('errors.KeveNegarApiException'),
                'message' => $e->errorMessage(),
            ]);
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            return new ErrorResource((object)[
                'error' => __('errors.KeveNegarHttpException'),
                'message' => $e->errorMessage(),
            ]);
        }
    }

    public function OtpLogin(OtpLoginRequest $request){
        
        if($this->phoneNumber == $request->phone){
            $user = User::where('phone', $request->phone)->first();
        //dd($request->phone);
        $dbCode = UserCodes::where([
            ['user-id','=',$user->id],
            ['expired','=',0],
            ['expire_date','>=',date('Y-m-d H:i:s')]
        ])->first();
        if(!$dbCode || !Hash::check($request->code, $dbCode->code)){
            return new ErrorResource((object)[
                'error' => __('errors.Credentials Are Incorrect'),
                'message' => __('errors.Credentials Are Incorrect'),
            ]);
        }
        UserCodes::where('user-id',$user->id)->update(['expired'=>1]);
        return new SuccessResource((object)['data'=>(object)['accessToken'=>$user->createToken('AccessToken')->accessToken,'refreshToken'=>'','tokenType'=>'Bearer']]);

        }
        else{
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Phone Number Is Invalid'),
            ]);
        }
        

    }

    public function Logout(Request $request ){
        try{
            $token = $request->user()->token();
            $token->revoke();
            return new SuccessResource((object)['data'=>'you are logged out successfully']);
        }catch(Exception $e){
            return new ErrorResource((object)[
                'error' => __('errors.Server Error Occured'),
                'message' => __('errors.Server Error Occured'),
            ]);
        }
    }

}
