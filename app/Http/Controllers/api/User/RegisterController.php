<?php

namespace App\Http\Controllers\api\User;

use App\Http\Requests\api\User\UserREgisterRequest;
use App\Shop\Customers\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function register(UserRegisterRequest $request){
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return [
            'status' => true,
            'message' => 'register successful',
            'token' => $customer->createToken('create')->accessToken,
        ];
    }
}
