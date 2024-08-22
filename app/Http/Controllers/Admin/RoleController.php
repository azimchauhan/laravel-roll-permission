<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Roles;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class RoleController extends Controller
{
    public function __construct() {
        $this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Role List';

            if (Auth::user()->can('role-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.role.create'),
                    'title' => 'Add Role',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Role List'
            );

            return view('admin.role.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $role = Roles::query();

        if ($request->has('filter')) {
            if ($request->filter['fltStatus'] != '') {
                $role->where('status', $request->filter['fltStatus']);
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $role->whereDate('created_at', $from_date);
                } else {
                    $role->whereBetween('created_at', [$from_date, $to_date]);
                }
            }
        }

        return DataTables::eloquent($role)
            ->addColumn('action', function($role) {
                $action = '';

                $action = '';
                if (Auth::user()->can('role-edit')) {
                    $action .= '<a href="'.route('admin.role.edit', $role->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('role-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.role.destroy').'" data-id="'.$role->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->editColumn('status', function ($role) {
                if (Auth::user()->can('role-edit')) {
                    $checkedAttr = $role->status == 1 ? 'checked' : '';
                    $status = '<div class="form-check form-switch form-switch-md mb-3" dir="ltr"> <input class="form-check-input js-switch" type="checkbox" data-id="' . $role->id . '" data-url="' . route('admin.role.change.status') . '" ' . $checkedAttr . '> </div>';
                } else {
                    $status = ($role->status == 1) ? 'Active' : 'InActive';
                }

                return $status;
            })
            ->editColumn('created_at', function($role) {
                return date('d/m/Y h:i A', strtotime($role->created_at));
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('display_name', function ($query, $order) {
                $query->orderBy('display_name', $order);
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('status', $order);
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create() {
        try {
            $data['page_title'] = 'Add Role';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('role-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.role.index'),
                    'title' => 'Role List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add Role'
            );

            $data['permissions'] = Permission::whereStatus(1)->get();

            return view('admin.role.create', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'name' => 'required',
            ];

            $messages = [
                'name.required' => 'The name field is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if ($request->has('role_id')) {
                    return redirect()->route('admin.role.edit', $request->role_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.role.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->has('role_id')) {
                    $role = Role::where('id', $request->role_id)->first();
                    $action = 'updated';
                } else {
                    $role = new Role();
                    $action = 'added';
                    $role->guard_name = 'web';
                }

                $role->name = Str::slug($request->name, "-");
                $role->display_name = $request->name;
                $role->status = ($request->has('status') && $request->status == 'on') ? 1 : 0;

                if ($role->save()) {
                    if (count($request->permissions) > 0) {
                        if ($request->has('role_id')) {
                            $role->syncPermissions($request->permissions);
                        } else {
                            foreach ($request->permissions as $permission) {
                                $role->givePermissionTo($permission);
                            }
                        }
                    }

                    Session::flash('alert-message', 'Role '.$action.' successfully.');
                    Session::flash('alert-class','success');
                    return redirect()->route('admin.role.index');
                } else {
                    Session::flash('alert-message', 'Role not '.$action.'.');
                    Session::flash('alert-class','error');

                    if ($request->has('role_id')) {
                        return redirect()->route('admin.role.edit', $request->role_id);
                    } else {
                        return redirect()->route('admin.role.create');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','error');

            if ($request->has('role_id')) {
                return redirect()->route('admin.role.edit', $request->role_id);
            } else {
                return redirect()->route('admin.role.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit Role';
            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('role-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.role.index'),
                    'title' => 'Role List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit Role'
            );

            $role = Role::find($id);

            if ($role) {
                $data['role'] = $role;
                $data['permissions'] = Permission::whereStatus(1)->get();

                return view('admin.role.create', $data);
            } else {
                return abort(404);
            }
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function destroy(Request $request) {
        try {
            if ($request->ajax()) {
                $role = Roles::where('id', $request->id)->first();

                if ($role->delete()) {
                    $return['success'] = true;
                    $return['message'] = "Role deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "Role not deleted.";
                }

                return response()->json($return);
            }
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function change_status(Request $request) {
        if ($request->ajax()) {
            try {
                $role = Roles::find($request->id);
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
}
