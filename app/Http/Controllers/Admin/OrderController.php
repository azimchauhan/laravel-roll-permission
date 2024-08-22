<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class OrderController extends Controller
{
    public function __construct() {
        $this->middleware('permission:order-list', ['only' => ['index']]);
        $this->middleware('permission:order-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:order-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:order-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Order List';

            if (Auth::user()->can('order-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.order.create'),
                    'title' => 'Add Order',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Order List'
            );

            $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                $q->where('id', '!=', 1);
            })->get();

            return view('admin.order.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $order = Order::query();

        if (!isSuperAdmin()) {
            $order->where('user_id', Auth::id());
        }

        if ($request->has('filter')) {
            if (isSuperAdmin()) {
                if ($request->filter['user_id'] != '') {
                    $order->where('user_id', $request->filter['user_id']);
                }
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $order->whereDate('created_at', $from_date);
                } else {
                    $order->whereBetween('created_at', [$from_date, $to_date]);
                }
            }
        }

        return DataTables::eloquent($order)
            ->addColumn('action', function($order) {
                $action = '';

                $action = '';
                if (Auth::user()->can('order-list')) {
                    $action .= '<a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm orderProducts" title="Order Products" data-order-id="'.$order->id.'"><i class="fas fa-bars"></i></a>&nbsp;';
                }

                if (Auth::user()->can('order-edit')) {
                    $action .= '<a href="'.route('admin.order.edit', $order->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('order-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.order.destroy').'" data-id="'.$order->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->addColumn('user', function($order) {
                return ($order->user) ? $order->user->name : '';
            })
            ->editColumn('grand_total', function ($order) {
                return number_format($order->grand_total, 2);
            })
            ->editColumn('created_at', function($order) {
                return date('d/m/Y h:i A', strtotime($order->created_at));
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('customer_name', function ($query, $order) {
                $query->orderBy('customer_name', $order);
            })
            ->orderColumn('customer_number', function ($query, $order) {
                $query->orderBy('customer_number', $order);
            })
            ->orderColumn('grand_total', function ($query, $order) {
                $query->orderBy('grand_total', $order);
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create() {
        try {
            $data['page_title'] = 'Add Order';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('order-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.order.index'),
                    'title' => 'Order List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add Order'
            );

            $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                $q->where('id', '!=', 1);
            })->get();

            $inventories = Inventory::whereStatus(1);

            if (!isSuperAdmin()) {
                $inventories->where('user_id', Auth::id());
            }

            $data['inventories'] = $inventories->get();

            return view('admin.order.create', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'customer_name' => 'required',
                'customer_number' => 'required',
            ];

            if (isSuperAdmin()) {
                $roles['user_id'] = 'required';
            }

            $messages = [
                'user_id.required' => 'The user field is required.',
                'customer_name.required' => 'The customer name field is required.',
                'customer_number.required' => 'The customer number field is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if ($request->order_id != '') {
                    return redirect()->route('admin.order.edit', $request->order_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.order.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->order_id != '') {
                    $order = Order::where('id', $request->order_id)->first();
                    $action = 'updated';
                } else {
                    $order = new Order();
                    $action = 'added';
                }

                $order->user_id = isSuperAdmin() ? $request->user_id : Auth::id();
                $order->customer_name = $request->customer_name;
                $order->customer_number = $request->customer_number;
                $order->grand_total = $request->grand_total;

                if ($order->save()) {
                    if ($request->order_id != '') {
                        OrderProduct::where('order_id', $request->order_id)->delete();
                    }

                    foreach ($request->orderProducts as $orderProduct) {
                        $orderProd = new OrderProduct();
                        $orderProd->order_id = $order->id;
                        $orderProd->inventory_id = $orderProduct['inventory_id'];
                        $orderProd->product_id = $orderProduct['product_id'];
                        $orderProd->selling_price = $orderProduct['selling_price'];
                        $orderProd->quantity = $orderProduct['quantity'];
                        $orderProd->total_amount = $orderProduct['total_amount'];
                        $orderProd->save();

                        $inventory = Inventory::find($orderProduct['inventory_id']);

                        if ($inventory && $inventory->quantity != 0) {
                            $inventory->quantity = $inventory->quantity - $orderProduct['quantity'];
                            $inventory->save();
                        }
                    }

                    Session::flash('alert-message', "Order ".$action." successfully.");
                    Session::flash('alert-class','success');

                    return redirect()->route('admin.order.index');
                } else {
                    Session::flash('alert-message', "Order not ".$action.".");
                    Session::flash('alert-class','error');

                    if ($request->order_id != '') {
                        return redirect()->route('admin.order.edit', $request->order_id);
                    } else {
                        return redirect()->route('admin.order.create');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','error');

            if ($request->order_id != '') {
                return redirect()->route('admin.order.edit', $request->order_id);
            } else {
                return redirect()->route('admin.order.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit Order';
            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('order-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.order.index'),
                    'title' => 'Order List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit Order'
            );

            $order = Order::find($id);

            if ($order) {
                $data['order'] = $order;
                $data['products'] = Product::whereStatus(1)->get();
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();

                $inventories = Inventory::whereStatus(1);

                if (!isSuperAdmin()) {
                    $inventories->where('user_id', Auth::id());
                }

                $data['inventories'] = $inventories->get();

                return view('admin.order.create', $data);
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
                $order = Order::where('id', $request->id)->first();

                if ($order) {
                    $order->delete();

                    $return['success'] = true;
                    $return['message'] = "Order deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "Order not found.";
                }

                return response()->json($return);
            }
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function productsDatatable(Request $request) {
        if ($request->ajax()) {
            $orderProduct = OrderProduct::where('order_id', $request->orderId);

            return DataTables::of($orderProduct)
                ->addColumn('product', function ($orderProduct) {
                    return ($orderProduct->product) ? $orderProduct->product->name : '';
                })
                ->filterColumn('product', function($orderProduct, $keyword) {
                    $orderProduct->whereHas('product', function($q) use($keyword) {
                        $q->where('name', 'like', '%'.$keyword.'%');
                    });
                })
                ->editColumn('selling_price', function ($orderProduct) {
                    return number_format($orderProduct->selling_price, 2);
                })
                ->editColumn('total_amount', function ($orderProduct) {
                    return number_format($orderProduct->total_amount, 2);
                })
                ->rawColumns([])
                ->make(true);
        }
    }
}
