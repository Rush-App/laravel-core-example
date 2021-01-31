<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseApiRequest;

class RegisterRequest extends BaseApiRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|unique:admins|max:50',
            'password' => 'required|max:30|confirmed',
        ];
    }
}
