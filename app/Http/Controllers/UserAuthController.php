<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        if ($user->active === 0) {
            return $this->sendMessage('Inactive account', ['Account suspended'], false, 401, true);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->sendMessage(['token' => $token, 'token_type' => 'Bearer', 'user' => $user]);

    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phonenumber' => "required|unique:users|min:11",
            'store_id' => 'integer|nullable|required_if:role,ATTENDANT',
            'address' => 'nullable',
            'role' => 'in:ATTENDANT,MANAGER,SUPERVISOR,DIRECTOR',
            'name' => 'required',
            'email' => 'required|email||unique:users',
            'password' => 'required|min:6',
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
    public function changePassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendMessage('Error validation', $validator->errors(), false, 422);
        }

        if (!is_null($user)) {
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->sendMessage('Incorrect Password', ['Incorrect Password'], false, 422);
            }
            $user->update([
                'password' => $request->password,
            ]);
            return $this->sendMessage('Password Updated');
        }

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
