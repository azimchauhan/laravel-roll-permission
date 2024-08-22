<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use File;
use Hash;
use Auth;

class ProfileController extends Controller
{
    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Profile';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Profile'
            );

            return view('admin.profile', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function update(Request $request) {
        try {
            if ($request->ajax()) {
                $rules = [
                    'name'  => 'required',
                    'email' => 'required'
                ];

                $messages = [
                    'name.required' => 'The name field is required.',
                    'email.required' => 'The email field is required.'
                ];

                if ($request->has('image')) {
                    $rules['image'] = 'required|mimes:jpg,jpeg,png,gif|max:2048';
                    $messages['image.required'] = 'The image field is required.';
                }

                $validator = Validator::make($request->all(), $rules, $messages);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->getMessageBag()->toArray()]);
                } else {
                    $user = Auth::user();
                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->updated_at = Carbon::now();

                    $userImageFolderPath = public_path('uploads/users/');
                    if (!File::isDirectory($userImageFolderPath)) {
                        File::makeDirectory($userImageFolderPath, 0777, true, true);
                    }

                    if ($image = $request->file('image')) {
                        if ($user->image != '') {
                            $userImage = public_path('uploads/users/'.$user->image);
                            if (File::exists($userImage)) {
                                unlink($userImage);
                            }
                        }

                        $destinationPath = 'uploads/users/';
                        $userImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                        $image->move($destinationPath, $userImage);
                        $user->image = $userImage;
                    }

                    if ($user->save()) {
                        $response['success'] = true;
                        $response['name'] = $user->name;
                        $response['email'] = $user->email;
                        $response['image'] = asset('uploads/users/'.$user->image);
                        $response['message'] = "Profile updated Successfully.";
                    } else {
                        $response['success'] = false;
                        $response['message'] = "error occured.";
                    }

                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();

            return response()->json($response);
        }
    }

    public function password_update(Request $request) {
        try {
            if ($request->ajax()) {
                $rules  = [
                    'current_password' => 'required',
                    'password' => 'required',
                    'password_confirmation' => 'required',
                ];

                $messages  = [
                    'current_password.required' => 'The current password field is required.',
                    'password.required' => 'The new password field is required.',
                    'password_confirmation.required' => 'The password confirmation field is required.',
                ];

                $validator = Validator::make($request->all(), $rules,$messages);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->getMessageBag()->toArray()]);
                } else {
                    $user = Auth::user();

                    if ($user) {
                        $user->password = bcrypt($request->password);
                        $user->updated_at = Carbon::now();

                        if ($user->save()) {
                            $response['success'] = true;
                            $response['message'] = "Password updated successfully.";
                        } else {
                            $response['success'] = false;
                            $response['message'] = "Password not updated.";
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = "User not found.";
                    }

                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();

            return response()->json($response);
        }
    }

    public function check_password(Request $request) {
        try {
            if ($request->ajax()) {
                $user = Auth::user();

                if ($user) {
                    if (Hash::check($request->current_password, $user->password)) {
                        $response = true;
                    } else {
                        $response = false;
                    }
                } else {
                    $response = false;
                }

                return response()->json($response);
            }
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();

            return response()->json($response);
        }
    }
}
