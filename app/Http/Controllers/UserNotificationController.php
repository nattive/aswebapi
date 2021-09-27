<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserNotificationResource;
use App\Models\UserNotification;

class UserNotificationController extends BaseController
{
    public function index()
    {
        $user = auth("sanctum")->user();
        return $this->sendMessage(UserNotificationResource::collection($user->userNotification()->where("is_read", false)->limit(5)->get()));
    }
    public function read($id)
    {
        $n = UserNotification::find($id);
        $n->update([
            'is_read' => true,
        ]);
        return $this->sendMessage('Notification Read');
    }
    public function show($id)
    {
        $n = UserNotification::find($id);
        $n->update([
            'is_read' => true,
        ]);
        return $this->sendMessage(new UserNotificationResource($n));

    }
}
