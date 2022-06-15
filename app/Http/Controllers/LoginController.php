<?php

namespace App\Http\Controllers;

use App\Http\Requests\OtpCodeRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\OtpLoginRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserCodes;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Kavenegar;

class LoginController extends Controller
{
    //
    // public function otpCodeRequest(OtpCodeRequest $request){
    //     $user = User::where('phone', $request -> phone) ->first();
    //     $token = rand(100000,999999);
    //     $now = strtotime(date('Y-m-d H:i:s'));
    //     $expire_date = date('Y-m-d H:i:s',$now+(env('OTP_EXPIRE_MINUTES')*60));
    //     $password = Hash::make($token);
    //     if(!$user){
    //         $validation = Validator::make(['name'=>$request->name],[
    //             'name' => 'required'
    //         ]);
    //         if($validation->fails()){
    //             return new ErrorResource((object)[
    //                 'error' => __('validation.RequestValidation'),
    //                 'message' => (new SerializeValidationErrorResponseHelper((object)$validation->errors()))->result,
    //             ]);
    //         }
    //        $user = User::create(["phone"=> $request -> phone,'name'=>$request->name, "password"=>$password]);
    //     }else{
    //         $user = User::where("phone",$request -> phone)->update(["password"=>$password]);
    //     }
    //     $user = User::where('phone',$request->phone)->first();
    //     $usercode = UserCodes::where("user-id",$user->id)->first();
    //     if(!$usercode){
    //         $usercode = UserCodes::create(["code" => $password,"user-id"=>$user->id,'expired'=>0,'expire_date'=>$expire_date]);
    //     }else{
    //         $usercode->update(['code'=>$password,'expired'=>0,'expire_date'=>$expire_date]);
    //     }
    //     try{
    //         $sender = env("OTP_SENDER");		   
    //         $receptor = strval($user -> phone);		
    //         $template = "login";
    //         $result = Kavenegar::VerifyLookup($receptor, $token, $token2=null, $token3=null, $template, $type = null);
    //         return new SuccessResource((object)['data'=>(object)$result[0]]);
    //     }
    //     catch(\Kavenegar\Exceptions\ApiException $e){
    //         // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
    //         return new ErrorResource((object)[
    //             'error' => __('errors.KeveNegarApiException'),
    //             'message' => $e->errorMessage(),
    //         ]);
    //     }
    //     catch(\Kavenegar\Exceptions\HttpException $e){
    //         // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
    //         return new ErrorResource((object)[
    //             'error' => __('errors.KeveNegarHttpException'),
    //             'message' => $e->errorMessage(),
    //         ]);
    //     }
    // }




    public function otpCodeRequest(OtpCodeRequest $request){

        $token = rand(100000,999999);
        $now = strtotime(date('Y-m-d H:i:s'));
        $expire_date = date('Y-m-d H:i:s',$now+(env('OTP_EXPIRE_MINUTES')*60));
        $password = Hash::make($token);
        $user = User::where('phone', $request -> phone) ->first();
        if(!$user){
            $user = User::create(["phone"=> $request->phone,"name"=>$request->phone, "password"=>$password]);
            $state = ['state'=>0, 'stateToString'=>__('userState.zero')];
        }else{
            $state = ['state'=>1, 'stateToString'=>__('userState.one')];
        }
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
            return new SuccessResource((object)['data'=>(object)[$result[0], $state]]);
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



    public function registerNewUser(RegisterUserRequest $request){
       try{    
        $id = Auth::user()->id;
        $user = User::find($id);
        $user->update(['name'=> $request->name]);
    }catch(Exception $e){
        return new ErrorResource((object)[
            'error' => __('errors.Credentials Are Incorrect'),
            'message' => __('errors.Credentials Are Incorrect'),
        ]);
    } 
    }





    public function OtpLogin(OtpLoginRequest $request){
        $user = User::where('phone', $request -> phone)->first();
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
