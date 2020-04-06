<?php

namespace App\Http\Controllers\api\User;

use App\Http\Requests\api\User\UserLoginRequest;
use App\Http\Requests\api\User\UserRegisterRequest;
use App\Http\Requests\api\User\UserRequest;
use App\Http\Resources\api\User\AddressResource;
use App\Shop\Customers\Customer;
use App\Shop\VerifyPhone\Verify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers ;
use Illuminate\Support\Facades\Validator;


class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function register(UserRegisterRequest $request){
        $customer = new Customer();
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->status = 1;
        $customer->password = bcrypt($request->password);
        $customer->save();
        $verify = new Verify();
        $verify->customer_id = $customer->id;
        $verify->phone = $customer->phone;
        $verify->token =mt_rand(1000, 9999);
        $verify->save();
        return response()->json([
            'token for verify(For Testing)'=> $verify,
            'message' => 'Successfully created user!','status' => true,
        ], 200);

    }

    public function verify(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required|max:4',
            'phone' => 'required|max:11',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 200);
        }
        $token_sent_by_user = $request->token;
        $phone_sent_by_user = $request->phone;
        $customer = Customer::all()->where('phone' , $phone_sent_by_user)->first();
        $verify = $customer->phone()->first();
        if($verify->status == 1){
            return ['status' => false , 'message'=>'already activate' ];
        }
        $created = new Carbon($verify->created_at);
        $now = Carbon::now();
        if($verify->attemp <=3 & $verify->token == $token_sent_by_user && $created->diffInMinutes($now) < 3){
            $verify->status =1;
            $verify->attemp +=1;
            $verify->save();
            return response()->json([
                'status' => true,
                'token' => $customer->createToken('create')->accessToken
            ],200);
        }
    }

    public function login(UserLoginRequest $request){
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('phone' , 'password');
        $credentials['status']=1;
        if(Auth::attempt($credentials)){
            $user =auth()->user();
            return [
                'status' => true,
                'message' =>'login successful',
                'token' => $user->createToken('create')->accessToken
            ];
        }
        $this->incrementLoginAttempts($request);
        return [
            'status' => false,
            'message' =>'login failed'
        ];
    }

    public function logout(Request $request){
        if(auth('api')->check()) {
            $request->user('api')->token()->revoke();
            return response()->json([
                'message' => 'Successfully logged out',
            ]);
        }
        return response()->json([
            'message' => 'you already log out',
        ]);

    }

    /**
     * @param Request $request
     * @return array
     * should be complete => user order history addresses with api resource
     */
    public function getUserData(UserRequest $request){
        //
    }


}
