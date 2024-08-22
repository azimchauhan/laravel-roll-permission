<?php
use Illuminate\Support\Facades\Auth;

if (!function_exists('sendResponse')) {
    function sendResponse($result = [], $message) {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($result)) {
            $response['data'] = $result;
        }

        return response()->json($response, 200);
    }
}

if (!function_exists('sendError')) {
    function sendError($error, $errorMessages = [], $code = 200) {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('super-admin')) {
                return true;
            }
        }

        return false;
    }
}
