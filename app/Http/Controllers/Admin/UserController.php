<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Roles;
use App\Models\User;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('permission:user-list', ['only' => ['index']]);
        $this->middleware('permission:user-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'User List';

            if (Auth::user()->can('user-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.user.create'),
                    'title' => 'Add User',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'User List'
            );

            $data['roles'] = Roles::whereStatus(1)->get();

            return view('admin.user.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $user = User::with('roles')->whereHas('roles', function ($q) {
            $q->where('id', '!=', 1);
        })->where('id', '!=', Auth::id());

        if ($request->has('filter')) {
            if (isset($request->filter['role_ids']) && $request->filter['role_ids'] != '') {
                $role_ids = $request->filter['role_ids'];

                $user->whereHas('roles', function ($q) use($role_ids) {
                    $q->whereIn('id', $role_ids);
                });
            }

            if ($request->filter['fltStatus'] != '') {
                $user->where('status', $request->filter['fltStatus']);
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $user->whereDate('created_at', $from_date);
                } else {
                    $user->whereBetween('created_at', [$from_date, $to_date]);
                }
            }
        }

        return DataTables::eloquent($user)
            ->addColumn('action', function ($user) {
                $action = '';
                if (Auth::user()->can('user-edit')) {
                    $action .= '<a href="'.route('admin.user.edit', $user->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('user-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.user.destroy').'" data-id="'.$user->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->addColumn('roles', function ($user) {
                $roles = '';
                $last_key = count($user->roles) - 1;

                foreach ($user->roles as $key => $role) {
                    $comma = ($last_key != $key) ? ', ' : '';
                    $roles .= $role->display_name.$comma;
                }

                return $roles;
            })
            ->editColumn('image', function ($user) {
                if ($user->image != '' && File::exists(public_path('uploads/users/' . $user->image))) {
                    $image = '<img src="' . asset('uploads/users/' . $user->image) . '" id="users" class="rounded-circle header-profile-user" alt="Avatar">';
                } else {
                    $image = '-';
                }
                return $image;
            })
            ->editColumn('status', function ($user) {
                $status = '';

                if (Auth::user()->can('user-edit')) {
                    $checkedAttr = $user->status == 1 ? 'checked' : '';
                    $status = '<div class="form-check form-switch form-switch-md mb-3" dir="ltr"> <input class="form-check-input js-switch" type="checkbox" data-id="' . $user->id . '" data-url="' . route('admin.user.change.status') . '" ' . $checkedAttr . '> </div>';
                } else {
                    $status = ($user->status == 1) ? 'Active' : 'InActive';
                }

                return $status;
            })
            ->editColumn('created_at', function($user) {
                return date('d/m/Y h:i A', strtotime($user->created_at));
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('name', $order);
            })
            ->orderColumn('email', function ($query, $order) {
                $query->orderBy('email', $order);
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->rawColumns(['action', 'image', 'status'])
            ->make(true);
    }

    public function create() {
        try {
            $data['page_title'] = 'Add User';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('user-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.user.index'),
                    'title' => 'User List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add User'
            );

            $data['roles'] = Roles::whereStatus(1)->get();

            return view('admin.user.create', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'role_id' => 'required',
                'name'    => 'required',
            ];

            if ($request->has('user_id') && $request->user_id == '') {
                if ($request->has('email') && $request->email != '') {
                    $rules['email'] = 'required|unique:users,email';
                }

                $rules['password'] = 'required|confirmed';
                $rules['password_confirmation'] = 'required';
            } else {
                if ($request->has('email') && $request->email != '') {
                    $rules['email'] = 'required|unique:users,email,'.$request->user_id;
                }
            }

            if ($request->has('image')) {
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:4096';
            }

            $messages = [
                'role_id.required'          => 'The role ids field is required.',
                'name.required'             => 'The name field is required.',
                'password.required'         => 'The password field is required.',
                'mobile_number.required'    => 'The mobile number field is required.',
                'mobile_number.unique'      => 'The mobile number already exists.',
                'email.unique'              => 'The email already exists.',
                'image.required'            => 'The image field is required.',
                'image.mimes'               => 'Please insert image only.',
                'image.max'                 => 'Image should be less than 4 MB.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if ($request->user_id != '') {
                    return redirect()->route('admin.user.edit', $request->user_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.user.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->user_id != '') {
                    $user = User::where('id', $request->user_id)->first();
                    $action = 'updated';
                } else {
                    $user = new User();
                    $action = 'added';
                }

                $user->name = $request->name;
                $user->email = $request->email;
                $user->status = ($request->has('status') && $request->status == 'on') ? 1 : 0;

                if ($request->has('password') && $request->password != '') {
                    $user->password = Hash::make($request->password);
                }

                if ($image = $request->file('image')) {
                    $userFolderPath = public_path('uploads/users/');
                    if (!File::isDirectory($userFolderPath)) {
                        File::makeDirectory($userFolderPath, 0777, true, true);
                    }

                    if ($user->image != '') {
                        $userImage = public_path('uploads/users/'.$user->image);

                        if (File::exists($userImage)) {
                            unlink($userImage);
                        }
                    }

                    $userImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                    $image->move($userFolderPath, $userImage);
                    $user->image = $userImage;
                }

                if ($user->save()) {
                    // if (is_array($request->role_ids)) {
                    //     foreach ($request->role_ids as $role_id) {
                    //         $user->assignRole($role_id);
                    //     }
                    // } else {
                    //     $user->assignRole($request->role_ids);
                    // }

                    $role = Role::where('id', $request->role_id)->first();

                    $user->assignRole($role->name);
                    Session::flash('alert-message', "User ".$action." successfully.");
                    Session::flash('alert-class','success');

                    return redirect()->route('admin.user.index');
                } else {
                    Session::flash('alert-message', "User not ".$action.".");
                    Session::flash('alert-class','error');

                    if ($request->user_id != '') {
                        return redirect()->route('admin.user.edit', $request->user_id);
                    } else {
                        return redirect()->route('admin.user.create');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','error');

            if ($request->has('user_id')) {
                return redirect()->route('admin.user.edit', $request->user_id);
            } else {
                return redirect()->route('admin.user.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit User';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('user-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.user.index'),
                    'title' => 'User List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit User'
            );

            $user = User::find($id);


            if ($user) {
                $data['roles'] = Role::where('id', '!=', 1)->get();
                $data['user'] = $user;

                $role_ids = [];
                foreach ($user->roles as $role) {
                    $role_ids[] = $role->id;
                }
                $data['role_ids'] = $role_ids;

                return view('admin.user.create', $data);
            } else {
                return abort(404);
            }
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function exists(Request $request){
        if (isset($request->id) && $request->id != '') {
            $result = User::where('id', '!=', $request->id)->where('email', $request->email)->count();
        } else {
            $result = User::where('email',$request->email)->count();
        }

        if ($result > 0) {
            return response()->json(false);
        } else {
            return response()->json(true);
        }
    }

    public function change_status(Request $request) {
        if ($request->ajax()) {
            try {
                $role = User::find($request->id);
                $role->status = $request->status;

                if ($role->save()) {
                    $response['success'] = true;
                    $response['message'] = "Status has been changed successfully.";
                } else {
                    $response['success'] = false;
                    $response['message'] = "Status has been changed unsuccessfully.";
                }

                return response()->json($response);
            } catch (\Exception $e) {
                return abort(404);
            }
        } else {
            return abort(404);
        }
    }

    public function destroy(Request $request) {
        try {
            if ($request->ajax()) {
                $user = User::where('id', $request->id)->first();

                if ($user) {
                    if ($user->image != '') {
                        $userImage = public_path('uploads/users/'.$user->image);

                        if (File::exists($userImage)) {
                            unlink($userImage);
                        }
                    }

                    $user->delete();

                    $return['success'] = true;
                    $return['message'] = "User deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "User not found.";
                }

                return response()->json($return);
            }
        } catch (\Exception $e) {
            return abort(404);
        }
    }
}
