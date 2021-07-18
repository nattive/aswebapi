<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends BaseController
{

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);
        // $data->fails()
        if (!Auth::attempt($request->only('email', 'password'))) {
           return $this->sendMessage('Invalid login details', ['Invalid login details'], false, 401, true);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->sendMessage(['token' => $token, 'token_type' => 'Bearer', 'user' => $user]);

    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phonenumber' => "required|unique:users",
            'store_id' => 'integer|nullable',
            'address' => 'nullable',
            'role' => 'in:ATTENDANT,MANAGER,SUPERVISOR,DIRECTOR',
            'name' => 'required',
            'email' => 'required|email||unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendMessage('Error validation', $validator->errors(), false, 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyAuthApp')->plainTextToken;
        $success['user'] = $user;

        return $this->sendMessage($success);

    }

    public function me()
    {
        $user = auth("sanctum")->user();
        if (is_null($user)) {
            return $this->sendMessage(null, ['Tou are not authenticated'], false, 401, true);
        }
        return $this->sendMessage($user);

    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();
        return $this->sendMessage('Logged out');

    }
}
