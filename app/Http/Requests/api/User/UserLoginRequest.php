<?php

namespace App\Http\Requests\api\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserLoginRequest
 * @package App\Http\Requests\api\User
 * @property String $email
 * @property String $password
 */
class UserLoginRequest extends FormRequest
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
            'email' => 'required|email|exists:customers',
            'password' => 'required'
        ];
    }
}
