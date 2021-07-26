<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    public function index()
    {
    }
    public function assign(Request $request)
    {
        $d = $request -> validate([
            'user_id' => 'required|integer',
             'role' => 'in:ATTENDANT,MANAGER,SUPERVISOR,DIRECTOR',
        ]);
        $user = User::findOrFail($request->user_id);
        $user->update(['role' => $request->role]);
        return $this->sendMessage('User access granted');
    }
}
