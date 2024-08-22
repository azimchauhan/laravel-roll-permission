<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Validator;
use File;

class AuthController extends Controller
{
    public function login(Request $request) {
        try {
            $rules = [
                'email' => 'required',
                'password' => 'required',
            ];

            $messages = [
                'email.required' => 'The mobile number field is required.',
                'password.required' => 'The password field is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return sendError('Validation Error.', $validator->errors());
            }

            $user = User::where('email', $request->email)->first();

            if ($user) {
                if ($user->status == 1) {
                    if (Hash::check($request->password, $user->password)) {
                        if (Auth::loginUsingId($user->id)) {
                            $tokenResult = $user->createToken('Auth Token');
                            $tokenResult->token->save();

                            $response['token']                      = $tokenResult->accessToken;
                            $response['user']['id']                 = $user->id;
                            $response['user']['name']               = $user->name;
                            $response['user']['email']              = $user->email;
                            $response['user']['created_at']         = date('d/m/Y', strtotime($user->created_at));

                            $user_img = '';
                            if ((isset($user->image)) && File::exists(public_path('uploads/users/'.$user->image))) {
                                $user_img = asset('uploads/users/'.$user->image);
                            }

                            $response['user']['image'] = $user_img;

                            return sendResponse($response, "Login Successfully");
                        } else {
                            return sendError('Something went wrong.', []);
                        }
                    } else {
                        return sendError('Wrong password.', []);
                    }
                } else {
                    return sendError('Account is inactive.', []);
                }
            } else {
                return sendError('Invalid credentials.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function change_password(Request $request) {
        try {
            $rules = [
                'current_password' => 'required',
                'new_password' => 'required',
                'confirm_password' => 'required|same:new_password',
            ];

            $messages = [
                'current_password.required' => 'The current password field is required.',
                'new_password.required' => 'The new password field is required.',
                'confirm_password.required' => 'The confirm password field is required.',
                'confirm_password.same' => 'The confirm password should match the new password.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return sendError('Validation Error.', $validator->errors());
            }

            $user = Auth::user();

            if (Hash::check($request->current_password , $user->password)) {
                if (!Hash::check($request->new_password , $user->password)) {
                    $user->password = Hash::make($request->new_password);

                    if ($user->save()) {
                        return sendResponse([], 'Password changed successfully.');
                    } else {
                        return sendError('Something went wrong.',[]);
                    }
                } else {
                    return sendError('New password can not be the old password!.',[]);
                }
            } else {
                return sendError('Current password does not matched!.',[]);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function logout(Request $request) {
        try {
            $token = $request->user()->token();
            $token->revoke();

            return sendResponse([], 'Logout successfully.');
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }
}
