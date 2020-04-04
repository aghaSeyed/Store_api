<?php

namespace App\Http\Requests\api\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserRegisterRequest
 * @package App\Http\Requests\api\User
 * @property String $name
 * @property String $email
 * @property String $password
 */
class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'password' => 'required|string|confirmed',
            'email' => 'required|unique:customers|email',
            'phone' => 'required|string|unique:customers,phone'
        ];
    }
}
