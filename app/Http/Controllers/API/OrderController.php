<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderProduct;
use App\Models\Inventory;
use App\Models\Order;
use Validator;
use Session;
use File;
use Auth;

class OrderController extends Controller
{
    public function order_list(Request $request) {
        try {
            $orders = Order::where('user_id', Auth::id());

            if ($request->has('from_date') && $request->from_date != '' && $request->has('to_date') && $request->to_date != '') {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));

                if ($from_date == $to_date) {
                    $orders->whereDate('created_at', $from_date);
                } else {
                    $orders->whereBetween('created_at', [$from_date, $to_date]);
                }
            }

            $limit = $request->input('per_page', 10);
            $offset = ($request->input('page', 1) - 1) * $limit;
            $totalRecords = $orders->count();

            $orders = $orders->offset($offset)->limit($limit)->get();

            if ($orders) {
                $response['orders'] = [];

                foreach ($orders as $key => $order) {
                    $response['orders'][$key]['id'] = $order->id;
                    $response['orders'][$key]['customer_name'] = $order->customer_name;
                    $response['orders'][$key]['customer_number'] = $order->customer_number;
                    $response['orders'][$key]['grand_total'] = $order->grand_total;
                    $response['orders'][$key]['created_at'] = date('d/m/Y h:i A', strtotime($order->created_at));

                    // foreach ($order->products as $k => $orderProduct) {
                    //     $response['orders'][$key]['products'][$k]['id'] = $orderProduct->id;
                    //     $response['orders'][$key]['products'][$k]['inventory_id'] = $orderProduct->inventory_id;
                    //     $response['orders'][$key]['products'][$k]['product']['id'] = ($orderProduct->product) ? $orderProduct->product->id : '';
                    //     $response['orders'][$key]['products'][$k]['product']['name'] = ($orderProduct->product) ? $orderProduct->product->name : '';
                    //     $response['orders'][$key]['products'][$k]['selling_price'] = $orderProduct->selling_price;
                    //     $response['orders'][$key]['products'][$k]['quantity'] = $orderProduct->quantity;
                    //     $response['orders'][$key]['products'][$k]['total_amount'] = $orderProduct->total_amount;
                    // }
                }

                $response['totalRecords'] = $totalRecords;
                $response['offset'] = (int)$request->input('page', 1);

                return sendResponse($response, 'Order data found.');
            } else {
                return sendError('Order not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function details($orderId) {
        try {
            $order = Order::find($orderId);

            if ($order) {
                $response['order']['id'] = $order->id;
                    $response['order']['customer_name'] = $order->customer_name;
                    $response['order']['customer_number'] = $order->customer_number;
                    $response['order']['grand_total'] = $order->grand_total;
                    $response['order']['created_at'] = date('d/m/Y h:i A', strtotime($order->created_at));

                    foreach ($order->products as $k => $orderProduct) {
                        $response['order']['products'][$k]['id'] = $orderProduct->id;
                        $response['order']['products'][$k]['inventory_id'] = $orderProduct->inventory_id;
                        $response['order']['products'][$k]['product']['id'] = ($orderProduct->product) ? $orderProduct->product->id : '';
                        $response['order']['products'][$k]['product']['name'] = ($orderProduct->product) ? $orderProduct->product->name : '';
                        $response['order']['products'][$k]['selling_price'] = $orderProduct->selling_price;
                        $response['order']['products'][$k]['quantity'] = $orderProduct->quantity;
                        $response['order']['products'][$k]['total_amount'] = $orderProduct->total_amount;
                    }

                return sendResponse($response, 'Order data found.');
            } else {
                return sendError('Order not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'customer_name' => 'required',
                'customer_number' => 'required',
                'grand_total' => 'required',
            ];

            $messages = [
                'customer_name.required' => 'The customer name field is required.',
                'customer_number.required' => 'The customer number field is required.',
                'grand_total.required' => 'The grand total field is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return sendError('Validation Error.', $validator->errors());
            } else {
                if ($request->has('order_id') && $request->order_id != '') {
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
                    if ($request->has('order_id') && $request->order_id != '') {
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

                    return sendResponse([], "Order ".$action." successfully.");
                } else {
                    return sendError("Order not ".$action.".", $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function destroy($orderId) {
        try {
            $order = Order::find($orderId);

            if ($order) {
                OrderProduct::where('order_id', $orderId)->delete();
                $order->delete();

                return sendResponse([], 'Order deleted successfully.');
            } else {
                return sendError('Order not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }
}
