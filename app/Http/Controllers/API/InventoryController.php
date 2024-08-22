<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use Validator;
use Session;
use File;
use Auth;

class InventoryController extends Controller
{
    public function inventory_list(Request $request) {
        try {
            $inventories = Inventory::where('user_id', Auth::id());

            if ($request->has('product_id') && $request->product_id != '') {
                $inventories->where('product_id', $request->product_id);
            }

            if ($request->has('status') && $request->status != '') {
                $inventories->whereStatus($request->status);
            }

            if (($request->has('quantity_start') && $request->quantity_start != '') && ($request->has('quantity_end') && $request->quantity_end != '')) {
                $quantity_start = $request->quantity_start;
                $quantity_end = $request->quantity_end;

                if ($quantity_start == $quantity_end) {
                    $inventories->where('quantity', $quantity_start);
                } else {
                    $inventories->whereBetween('quantity', [$quantity_start, $quantity_end]);
                }
            }

            if ($request->has('from_date') && $request->from_date != '' && $request->has('to_date') && $request->to_date != '') {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));

                if ($from_date == $to_date) {
                    $inventories->whereDate('created_at', $from_date);
                } else {
                    $inventories->whereBetween('created_at', [$from_date, $to_date]);
                }
            }

            $limit = $request->input('per_page', 10);
            $offset = ($request->input('page', 1) - 1) * $limit;
            $totalRecords = $inventories->count();

            $inventories = $inventories->offset($offset)->limit($limit)->get();

            if ($inventories) {
                $response['inventories'] = [];

                foreach ($inventories as $key => $inventory) {
                    $response['inventories'][$key]['id'] = $inventory->id;
                    $response['inventories'][$key]['product'] = ($inventory->product) ? $inventory->product->name : '';
                    $response['inventories'][$key]['quantity'] = $inventory->quantity;
                    $response['inventories'][$key]['purchase_price'] = $inventory->purchase_price;
                    $response['inventories'][$key]['selling_price'] = $inventory->selling_price;
                    $response['inventories'][$key]['status'] = $inventory->status;
                    $response['inventories'][$key]['notes'] = $inventory->notes;
                    $response['inventories'][$key]['created_at'] = date('d/m/Y h:i A', strtotime($inventory->created_at));

                    $envMinInvQty = env('MIN_INVENTORY_QTY');
                    $isOutOfStock = false;

                    if ($inventory->quantity <= $envMinInvQty) {
                        $isOutOfStock = true;
                    }

                    $response['inventories'][$key]['is_out_of_stock'] = $isOutOfStock;
                }

                $response['totalRecords'] = $totalRecords;
                $response['offset'] = (int)$request->input('page', 1);

                return sendResponse($response, 'Inventory data found.');
            } else {
                return sendError('Inventory not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function details($inventoryId) {
        try {
            $inventory = Inventory::find($inventoryId);

            if ($inventory) {
                $response['inventory']['id'] = $inventory->id;
                $response['inventory']['product']['id'] = ($inventory->product) ? $inventory->product->id : '';
                $response['inventory']['product']['name'] = ($inventory->product) ? $inventory->product->name : '';
                $response['inventory']['quantity'] = $inventory->quantity;
                $response['inventory']['purchase_price'] = $inventory->purchase_price;
                $response['inventory']['selling_price'] = $inventory->selling_price;
                $response['inventory']['status'] = $inventory->status;
                $response['inventory']['notes'] = $inventory->notes;
                $response['inventory']['created_at'] = date('d/m/Y h:i A', strtotime($inventory->created_at));

                $envMinInvQty = env('MIN_INVENTORY_QTY');
                $isOutOfStock = false;

                if ($inventory->quantity <= $envMinInvQty) {
                    $isOutOfStock = true;
                }

                $response['inventory']['is_out_of_stock'] = $isOutOfStock;

                return sendResponse($response, 'Inventory data found.');
            } else {
                return sendError('Inventory not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'product_id' => 'required',
                'quantity' => 'required',
                'purchase_price' => 'required',
                'selling_price' => 'required',
            ];

            $messages = [
                'product_id.required' => 'The product field is required.',
                'quantity.required' => 'The quantity field is required.',
                'purchase_price.required' => 'The purchase price field is required.',
                'selling_price.required' => 'The selling price field is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return sendError('Validation Error.', $validator->errors());
            } else {
                if ($request->has('inventory_id') && $request->inventory_id != '') {
                    $inventory = Inventory::where('id', $request->inventory_id)->first();
                    $action = 'updated';
                } else {
                    $inventory = new Inventory();
                    $action = 'added';
                }

                $inventory->user_id = Auth::id();
                $inventory->product_id = $request->product_id;
                $inventory->quantity = $request->quantity;
                $inventory->base_quantity = $request->base_quantity;
                $inventory->purchase_price = $request->purchase_price;
                $inventory->selling_price = $request->selling_price;
                $inventory->notes = $request->notes;
                $inventory->status = ($request->has('status') && $request->status == 1) ? 1 : 0;

                if ($inventory->save()) {
                    return sendResponse([], 'Inventory '.$action.' successfully.');
                } else {
                    return sendError('Inventory not '.$action.'.', []);
                }
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function destroy($inventoryId) {
        try {
            $inventory = Inventory::find($inventoryId);

            if ($inventory) {
                $inventory->delete();

                return sendResponse([], 'Inventory deleted successfully.');
            } else {
                return sendError('Inventory not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function change_status($inventoryId) {
        try {
            $inventory = Inventory::find($inventoryId);

            if ($inventory) {
                $inventory->status = ($inventory->status == 1) ? 0 : 1;

                if ($inventory->save()) {
                    return sendResponse([], 'Status has been changed successfully.');
                } else {
                    return sendError('Status not update.', []);
                }
            } else {
                return sendError('Inventory not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }
}
