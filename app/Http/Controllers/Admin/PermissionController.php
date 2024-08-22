<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Permissions;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class PermissionController extends Controller
{
    public function __construct() {
        $this->middleware('permission:permission-list', ['only' => ['index']]);
        $this->middleware('permission:permission-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Permission List';

            if (Auth::user()->can('permission-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.permission.create'),
                    'title' => 'Add Permission',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Permission List'
            );

            return view('admin.permission.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $permisssion = Permissions::query();

        if ($request->has('filter')) {
            if ($request->filter['fltStatus'] != '') {
                $permisssion->where('status', $request->filter['fltStatus']);
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $permisssion->whereDate('created_at', $from_date);
                } else {
                    $permisssion->whereBetween('created_at', [$from_date, $to_date]);
                }
            }
        }

        return DataTables::eloquent($permisssion)
            ->addColumn('action', function($permisssion) {
                $action = '';

                $action = '';
                if (Auth::user()->can('permission-edit')) {
                    $action .= '<a href="'.route('admin.permission.edit', $permisssion->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('permission-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.permission.destroy').'" data-id="'.$permisssion->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->editColumn('status', function ($permisssion) {
                if (Auth::user()->can('permission-edit')) {
                    $checkedAttr = $permisssion->status == 1 ? 'checked' : '';
                    $status = '<div class="form-check form-switch form-switch-md mb-3" dir="ltr"> <input class="form-check-input js-switch" type="checkbox" data-id="' . $permisssion->id . '" data-url="' . route('admin.permission.change.status') . '" ' . $checkedAttr . '> </div>';
                } else {
                    $status = ($permisssion->status == 1) ? 'Active' : 'InActive';
                }

                return $status;
            })
            ->editColumn('created_at', function($permisssion) {
                return date('d/m/Y h:i A', strtotime($permisssion->created_at));
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
            $data['page_title'] = 'Add Permission';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('permission-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.permission.index'),
                    'title' => 'Permission List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add Permission'
            );

            return view('admin.permission.create', $data);
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
                if ($request->has('permission_id')) {
                    return redirect()->route('admin.permission.edit', $request->permission_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.permission.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->has('permission_id')) {
                    $permisssion = Permissions::where('id', $request->permission_id)->first();
                    $action = 'updated';
                } else {
                    $permisssion = new Permissions();
                    $action = 'added';
                    $permisssion->guard_name = 'web';
                }

                $permisssion->name = Str::slug($request->name, "-");
                $permisssion->display_name = $request->name;
                $permisssion->status = ($request->has('status') && $request->status == 'on') ? 1 : 0;
                $permisssion->save();

                Session::flash('alert-message', 'Permission '.$action.' successfully.');
                Session::flash('alert-class','success');
                return redirect()->route('admin.permission.index');
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','success');

            if ($request->has('permission_id')) {
                return redirect()->route('admin.permission.edit', $request->permission_id);
            } else {
                return redirect()->route('admin.permission.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit Permission';
            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('permission-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.permission.index'),
                    'title' => 'Permission List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit Permission'
            );

            $permisssion = Permissions::find($id);

            if ($permisssion) {
                $data['permission'] = $permisssion;
                return view('admin.permission.create', $data);
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
                $permisssion = Permissions::where('id', $request->id)->first();

                if ($permisssion->delete()) {
                    $return['success'] = true;
                    $return['message'] = "Permission deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "Permission not deleted.";
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
                $permisssion = Permissions::find($request->id);
                $permisssion->status = $request->status;

                if ($permisssion->save()) {
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
