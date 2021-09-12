<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use ApiResponse;
    
    public function userLogin(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $roles = [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];
        $validator = Validator::make($credentials, $roles);
        if($validator->fails()){
            return $this->error($validator->errors(), 'validation Error', 400);
        }

        if(auth()->attempt($credentials)){

            $user = auth()->user();
            $data = [
                'user' => $user,
                'token' => $user->createToken('Personal Access Token', ['user'])->accessToken,
            ];
            return $this->success($data, 'Logged in Successfully');
        }

        return $this->error([], 'Email Or Password Is Incorrect', 403);
    }
}
