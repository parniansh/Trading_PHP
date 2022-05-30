<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\UserCodes;
use Illuminate\Support\Facades\Hash;
use Kavenegar;

class LoginController extends Controller
{
    //

    public function login(Request $request){
        

        $request -> validate(
            ['email' => ['required','email'],
            'password' => ['required']    ]    
        );

        $user = User::where('email', $request ->email) -> first();

        if(!$user || !Hash::check($request->password, $user->password)){

            throw ValidationException::withMessages ([
                'email'=> ['credentials are incorrect']
            ]);
        }

       return $user ->createToken('Auth Token') ->accessToken;
    }



    public function otpCodeRequest(Request $request){
        $request -> validate([
            'phone' => ['required','min:11','max:11','regex:/^([0-9\s\-\+\(\)]*)$/'],
        ]);

        $user = User::where('phone', $request -> phone) ->first();

        $token = rand(100000,999999);
        $now = strtotime(date('Y-m-d H:i:s'));
        $expire_date = date('Y-m-d H:i:s',$now+(env('OTP_EXPIRE_MINUTES')*60));
        $password = Hash::make($token);

        if(!$user){
            $request -> validate([
                'name' => ['required'],
            ]);
           $user = User::create(["phone"=> $request -> phone,'name'=>$request->name, "password"=>$password]);
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
            return $result;

        }
        catch(\Kavenegar\Exceptions\ApiException $e){
            // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
            echo $e->errorMessage();
        }
        catch(\Kavenegar\Exceptions\HttpException $e){
            // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
            echo $e->errorMessage();
        }
        

    }

    public function OtpLogin(Request $request){

        $validate = $request->validate([
            'phone' => ['required','min:11','max:11','regex:/^([0-9\s\-\+\(\)]*)$/'],
            'code' => 'required | min:6 | max:6'
        ]);

        $user = User::where('phone', $request -> phone)->first();

        
        $dbCode = UserCodes::where([
            ['user-id','=',$user->id],
            ['expired','=',0],
            ['expire_date','>=',date('Y-m-d H:i:s')]
        ])->first();
        if(!$dbCode || !Hash::check($request->code, $dbCode->code)){
            throw ValidationException::withMessages ([
                'userOtp'=> ['credentials are incorrect']
            ]);
        }

        $userCodes = UserCodes::where('user-id',$user->id)->update(['expired'=>1]);
        return (object)['accessToken'=>$user->createToken('AccessToken')->accessToken,'refreshToken'=>'','tokenType'=>'Bearer'];

    }


    public function Logout(Request $request ){
       
        $token = $request->user()->token();
        $token->revoke();
        $response = ["message"=>"you are logged out successfully"];
        return response($response,200);    


    }
}
