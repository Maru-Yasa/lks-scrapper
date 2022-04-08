<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\ItemNotFoundException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /*
        ##################################################
        # config CRUD utilities
        ##################################################    
    */

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 403);
        }

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password)
        ]);

        return response()->json([
            'status' => "success",
            'data' => $user,
            'msg' => 'Successfuly register, pls do login',
        ],200);

    }

    public function login(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 403);
        }

        if(!Auth::attempt($req->only('email', 'password'))){
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => 'Email or password invalid',
            ], 403);
        }

        $user = User::all()->where('email',$req->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => "success",
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ],
            'msg' => 'Successfully logged-in as '.$user->name,
        ],200);

    }

    public function logout(Request $req)
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => "success",
            'data' => [],
            'msg' => 'Successfully logout',
        ],200);

    }

    public function update(Request $req)
    {

        try {
            $user = User::all()->where('id', auth()->user()->id)->firstOrFail();
            $user->update($req->except(['id']));
            return response()->json([
                'status' => "success",
                'data' => $user,
                'msg' => "Success updating user",
            ], 200);
        } catch (ItemNotFoundException $e) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "user with id $req->id not found",
            ], 400);
        }
    }

    public function getUser(Request $req)
    {
        return response()->json([
            'status' => "success",
            'data' => auth()->user(),
            'msg' => '',
        ],200);
    }

}
