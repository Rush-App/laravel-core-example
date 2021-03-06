<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseApiRequest;

class RegisterRequest extends BaseApiRequest
{
    public function rules()
    {
        return [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:50',
            'password' => 'required|max:30|confirmed',
        ];
    }
}
