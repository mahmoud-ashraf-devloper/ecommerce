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
        $roles = [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];

        
        $validator = Validator::make($request->all() , $roles);
 
        $credentials = $request->only(['email', 'password']);
        
        if($validator->fails()){
            return $this->error($validator->errors(), 400 ,'Validation Error');
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
            return $this->error([] , 401 ,'Email or Password is incorrect');
        }

    }
}
