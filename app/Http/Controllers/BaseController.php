<?php

namespace App\Http\Controllers;

class BaseController extends Controller
{
    public function sendMessage($message, $errorMessage = [], $success = true, $code = 200, $failAuth = false)
    {
        if ($success == true) {
            return response()->json([
                'message' => $message,
                'success' => 'true',
            ], $code);
        }
        return response()->json([
            'errors' => $errorMessage,
            'success' => 'false',
            'failedAuth' =>  $failAuth
        ], $code);

    }
}
