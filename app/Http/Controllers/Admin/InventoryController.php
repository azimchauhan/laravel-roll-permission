<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class InventoryController extends Controller
{
    public function __construct() {
        $this->middleware('permission:inventory-list', ['only' => ['index']]);
        $this->middleware('permission:inventory-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:inventory-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:inventory-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Inventory List';

            if (Auth::user()->can('inventory-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.inventory.create'),
                    'title' => 'Add Inventory',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Inventory List'
            );

            $products = Product::whereStatus(1);

            if (!isSuperAdmin()) {
                $products->where('user_id', Auth::id());
            }

            $data['products'] = $products->get();

            if (isSuperAdmin()) {
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();
            }

            return view('admin.inventory.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $inventory = Inventory::query();

        if (!isSuperAdmin()) {
            $inventory->where('user_id', Auth::id());
        }

        if ($request->has('filter')) {
            if ($request->filter['fltStatus'] != '') {
                $inventory->where('status', $request->filter['fltStatus']);
            }

            if (isset($request->filter['user_id']) && $request->filter['user_id'] != '') {
                $inventory->where('user_id', $request->filter['user_id']);
            }

            if ($request->filter['product_id'] != '') {
                $inventory->where('product_id', $request->filter['product_id']);
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $inventory->whereDate('created_at', $from_date);
                } else {
                    $inventory->whereBetween('created_at', [$from_date, $to_date]);
                }
            }

            if ((isset($request->filter['quantity_from']) && isset($request->filter['quantity_to'])) && ($request->filter['quantity_from'] != null && $request->filter['quantity_to'] != null)) {
                $quantity_from = $request->filter['quantity_from'];
                $quantity_to = $request->filter['quantity_to'];

                if ($quantity_from == $quantity_to) {
                    $inventory->where('quantity', $quantity_from);
                } else {
                    $inventory->whereBetween('quantity', [$quantity_from, $quantity_to]);
                }
            }
        }

        return DataTables::eloquent($inventory)
            ->addColumn('action', function($inventory) {
                $action = '';

                $action = '';
                if (Auth::user()->can('inventory-edit')) {
                    $action .= '<a href="'.route('admin.inventory.edit', $inventory->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('inventory-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.inventory.destroy').'" data-id="'.$inventory->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->addColumn('user', function($inventory) {
                return ($inventory->user) ? $inventory->user->name : '';
            })
            ->addColumn('product', function($inventory) {
                return ($inventory->product) ? $inventory->product->name : '';
            })
            ->editColumn('purchase_price', function ($inventory) {
                return number_format($inventory->purchase_price, 2);
            })
            ->editColumn('selling_price', function ($inventory) {
                return number_format($inventory->selling_price, 2);
            })
            ->editColumn('status', function ($inventory) {
                if (Auth::user()->can('inventory-edit')) {
                    $checkedAttr = $inventory->status == 1 ? 'checked' : '';
                    $status = '<div class="form-check form-switch form-switch-md mb-3" dir="ltr"> <input class="form-check-input js-switch" type="checkbox" data-id="' . $inventory->id . '" data-url="' . route('admin.inventory.change.status') . '" ' . $checkedAttr . '> </div>';
                } else {
                    $status = ($inventory->status == 1) ? 'Active' : 'InActive';
                }

                return $status;
            })
            ->editColumn('created_at', function($inventory) {
                return date('d/m/Y h:i A', strtotime($inventory->created_at));
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('quantity', function ($query, $order) {
                $query->orderBy('quantity', $order);
            })
            ->orderColumn('purchase_price', function ($query, $order) {
                $query->orderBy('purchase_price', $order);
            })
            ->orderColumn('selling_price', function ($query, $order) {
                $query->orderBy('selling_price', $order);
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
            $data['page_title'] = 'Add Inventory';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('inventory-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.inventory.index'),
                    'title' => 'Inventory List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add Inventory'
            );

            $data['products'] = Product::whereStatus(1)->get();
            $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                $q->where('id', '!=', 1);
            })->get();


            return view('admin.inventory.create', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'product_id' => 'required',
                'quantity' => 'required',
                // 'purchase_price' => 'required',
                'selling_price' => 'required',
            ];

            if (isSuperAdmin()) {
                $rules['user_id'] = 'required';
            }

            $messages = [
                'user_id.required' => 'The user field is required.',
                'product_id.required' => 'The product field is required.',
                'quantity.required' => 'The quantity field is required.',
                // 'purchase_price.required' => 'The purchase price field is required.',
                'selling_price.required' => 'The selling price field is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if ($request->has('inventory_id')) {
                    return redirect()->route('admin.inventory.edit', $request->inventory_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.inventory.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->has('inventory_id')) {
                    $inventory = Inventory::where('id', $request->inventory_id)->first();
                    $action = 'updated';
                } else {
                    $inventory = new Inventory();
                    $action = 'added';
                }

                $inventory->user_id = isSuperAdmin() ? $request->user_id : Auth::id();
                $inventory->product_id = $request->product_id;
                $inventory->quantity = $request->quantity;
                $inventory->base_quantity = $request->quantity;
                $inventory->purchase_price = ($request->purchase_price != null) ? $request->purchase_price : 0;
                $inventory->selling_price = ($request->selling_price != null) ? $request->selling_price : 0;
                $inventory->notes = $request->notes;
                $inventory->status = ($request->has('status') && $request->status == 'on') ? 1 : 0;

                if ($inventory->save()) {
                    Session::flash('alert-message', "Inventory ".$action." successfully.");
                    Session::flash('alert-class','success');

                    return redirect()->route('admin.inventory.index');
                } else {
                    Session::flash('alert-message', "Inventory not ".$action.".");
                    Session::flash('alert-class','error');

                    if ($request->has('inventory_id')) {
                        return redirect()->route('admin.inventory.edit', $request->inventory_id);
                    } else {
                        return redirect()->route('admin.inventory.create');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','error');

            if ($request->has('inventory_id')) {
                return redirect()->route('admin.inventory.edit', $request->inventory_id);
            } else {
                return redirect()->route('admin.inventory.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit Inventory';
            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('inventory-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.inventory.index'),
                    'title' => 'Inventory List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit Inventory'
            );

            $inventory = Inventory::find($id);

            if ($inventory) {
                $data['inventory'] = $inventory;
                $data['products'] = Product::whereStatus(1)->get();
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();

                return view('admin.inventory.create', $data);
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
                $inventory = Inventory::where('id', $request->id)->first();

                if ($inventory) {
                    $inventory->delete();

                    $return['success'] = true;
                    $return['message'] = "Inventory deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "Inventory not found.";
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
                $inventory = Inventory::find($request->id);
                $inventory->status = $request->status;

                if ($inventory->save()) {
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
