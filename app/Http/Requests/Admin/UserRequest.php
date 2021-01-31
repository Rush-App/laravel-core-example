<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends BaseApiRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|max:50|unique:users,email,'.Auth::guard('admin')->id(),
            'name' => 'required|max:20',
        ];
    }
}
