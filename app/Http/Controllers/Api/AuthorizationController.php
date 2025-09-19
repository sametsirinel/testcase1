<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthorizationController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where("email", $request->email)->first();

        if(!($user instanceof User) || !Hash::check($request->password, $user->password)){
            throw new AuthorizationException();
        }

        $token = $user->createToken("api")->plainTextToken;

        return $this->success([
            "user" => $user,
            "token" => $token
        ]);
    }
}
