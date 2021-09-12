<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
class LoginController extends Controller
{

    use ApiResponse;

    public function adminLogin(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $roles = [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];

        $validator = Validator::make($credentials , $roles);

        if($validator->fails()){
            return $this->error($validator->errors(), 'Validation Error', 400);
        }
        if(auth()->guard('admin')->attempt($credentials)){
 
            config(['auth.guards.api.provider' => 'admin']);
            $admin = Auth('admin')->user();
            
            $data = [
                'admin' => $admin,
                'token' => $admin->createToken('Personal Access Token',['admin'])->accessToken,
            ];

            return $this->success($data);
        }else{
            return $this->error([],'Email or Password is incorrect',401);
        }

    }
}
