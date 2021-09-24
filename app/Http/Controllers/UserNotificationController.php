<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserNotificationResource;
use Illuminate\Http\Request;

class UserNotificationController extends BaseController
{
    public function index()
    {
        $user = auth("sanctum")->user();
        return $this->sendMessage(UserNotificationResource::collection($user ->userNotification()->limit(5)->get()));
    }
}
