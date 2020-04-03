<?php

namespace App\Http\Controllers\api\User;

use App\Http\Requests\api\User\UserLoginRequest;
use App\Http\Requests\api\User\UserRegisterRequest;
use App\Http\Requests\api\User\UserRequest;
use App\Shop\Customers\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers ;


class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function register(UserRegisterRequest $request){
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return response()->json([
            'message' => 'Successfully created user!','status' => true,'token' => $customer->createToken('create')->accessToken,
        ], 201);

    }

    public function login(UserLoginRequest $request){
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('email' , 'password');
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
        if(auth('api')->check()){
        $user = $request->user('api');
        $addresses=$user->addresses()->where('status',1)->first();
        return[
            'name' => $user->name,
            'email' => $user->email,
            'address_1' => $addresses->address_1,
            'address_2' => $addresses->address_2,
            'city' => $addresses->city,
            'phone' =>$addresses->phone,
        ];}
        return ['status'=>false,'message'=>'user didnt login'];
    }


}
