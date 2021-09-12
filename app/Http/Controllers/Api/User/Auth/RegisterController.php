<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class RegisterController extends Controller
{
    use ApiResponse;
    
    public function userRegister(Request $request)
    {
        $rules = [
            'name' => 'required|string|min:5|max:255',
            'email'=> 'required|email|unique:users',
            'password'=> 'required|min:6',
            'confirmPassword'=> 'required|same:password',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return $this->error($validator->errors(), 'Validation Error', 400);
        }
        try {
            $data = $request->only(['name', 'email']);
            $data['password'] = Hash::make($request->password);

            $userCreated = User::create($data);
            if($userCreated){
                $response = [
                    'user' => $userCreated,
                    'token' => $userCreated->createToken('Personal Access Token',['user'])->accessToken,
                ];
                return $this->success($response, 'User Created Successfully');
            }else{
                return $this->error([],'Something Went Wrong Please Try again later', 500);
            }

        } catch (\Exception $ex) {
            return $this->error($ex->getMessage(), 'Something Went Wrong', $ex->getCode());
        }
    }
}
