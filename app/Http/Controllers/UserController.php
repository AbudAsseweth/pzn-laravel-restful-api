<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $attrs = $request->validated();

        $user = User::create($attrs);

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request)
    {
        $request->validated();

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw new HttpResponseException(response([
                "errors" => [
                    "username" => "Your credentials is not match.",
                ],
            ], 401));
        }

        $user->token = (string) Str::uuid();
        $user->save();

        return new UserResource($user);
    }

    public function currentUser()
    {
        $user = Auth::user();

        return new UserResource($user);
    }

    public function update(UserUpdateRequest $request)
    {
        $user = Auth::user();

        if (isset($request->username)) {
            $user->username = $request->username;
        }

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return new UserResource($user);
    }
}
