<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendMessage(User::all());
    }

    public function changeStore(Request $request)
    {
        $d = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'store_id' => 'required|exists:stores,id',
        ]);
        if ($d->fails()) {
            return $this->sendMessage('Error validation', $d->errors(), false, 422);
        }
        $user = User::find($request->user_id);
        $user->update([
            'store_id' => $request->store_id,
        ]);
        return $this->sendMessage("User store permission updated");
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'phonenumber' => "unique:users",
            'store_id' => 'integer|nullable',
            'address' => 'nullable',
            'name' => 'nullable',
            'email' => 'email||unique:users',
        ]);
        if ($validator->fails()) {
            return $this->sendMessage('Error validation', $validator->errors(), false, 422);
        }

        $user = User::find($id);
        $user->update($validator->validated());

        return $this->sendMessage('User Updated');

    }

    public function deactivate($user)
    {
        $user = User::where('id', $user)->first();

        $user->active = 0 ;
        $user->save() ;

        return $this->sendMessage( $user);

    }
    public function activate( $user)
    {
        $user = User::find($user);
        $user->update([
            'active' => true,
        ]);
        return $this->sendMessage("User  updated");

    }
}
