<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends BaseApiRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|max:50|unique:users,email,'.Auth::id(),
            'name' => 'required|max:20',
        ];
    }
}
