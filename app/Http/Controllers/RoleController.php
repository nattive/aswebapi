<?php

namespace App\Http\Controllers;

use App\Models\Store;
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
             "store_id" => 'required_if:role,MANAGER'
        ]);
        if ($request->role == 'MANAGER') {
            $store = Store::find($request->store_id);
            if (!is_null($store)) {
                $store -> update([ 'supervisor_id' => $request -> user_id ]);
            }
        }
        $user = User::findOrFail($request->user_id);
        $user->update(['role' => $request->role]);
        return $this->sendMessage('User access granted');
    }
}
