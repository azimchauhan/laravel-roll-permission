<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Validator;
use Session;
use File;
use Auth;

class ProductController extends Controller
{
    public function product_list(Request $request) {
        try {
            $products = Product::where('user_id', Auth::id());

            if ($request->has('name') && $request->name != '') {
                $products = $products->where('name', 'like', '%'.$request->name.'%');
            }

            if ($request->has('parent_category_id') && $request->parent_category_id != '') {
                $parent_category_id = $request->parent_category_id;

                $products = $products->whereHas('category', function ($q) use ($parent_category_id) {
                    $q->where('categories.parent_id', $parent_category_id);
                });
            }

            if ($request->has('sub_category_id') && $request->sub_category_id != '') {
                $products = $products->where('category_id', $request->sub_category_id);
            }

            if ($request->has('status') && $request->status != '') {
                $products = $products->whereStatus($request->status);
            }

            if ($request->has('from_date') && $request->from_date != '' && $request->has('to_date') && $request->to_date != '') {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));

                if ($from_date == $to_date) {
                    $products->whereDate('created_at', $from_date);
                } else {
                    $products->whereBetween('created_at', [$from_date, $to_date]);
                }
            }

            $limit = $request->input('per_page', 10);
            $offset = ($request->input('page', 1) - 1) * $limit;
            $totalRecords = $products->count();

            $products = $products->offset($offset)->limit($limit)->get();

            if ($products) {
                $response['products'] = [];

                foreach ($products as $key => $product) {
                    $response['products'][$key]['id'] = $product->id;
                    $response['products'][$key]['parent_category'] = (isset($product->category) && isset($product->category->parentCategory)) ? $product->category->parentCategory->name : '';
                    $response['products'][$key]['sub_category'] = ($product->category) ? $product->category->name : '';
                    $response['products'][$key]['name'] = $product->name;
                    $response['products'][$key]['purchase_price'] = $product->purchase_price;
                    $response['products'][$key]['selling_price'] = $product->selling_price;
                    $response['products'][$key]['status'] = $product->status;
                    $response['products'][$key]['created_at'] = date('d/m/Y h:i A', strtotime($product->created_at));

                    $productImg = '';
                    if ($product->image != '' && File::exists(public_path('uploads/product/'.$product->image))) {
                        $productImg = asset('uploads/product/'.$product->image);
                    }

                    $response['products'][$key]['image'] = $productImg;
                }

                $response['totalRecords'] = $totalRecords;
                $response['offset'] = (int)$request->input('page', 1);

                return sendResponse($response, 'Product data found.');
            } else {
                return sendError('Products not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function details($productId) {
        try {
            $product = Product::find($productId);

            if ($product) {
                $response['product']['id'] = $product->id;
                $response['product']['parent_category']['id'] = (isset($product->category) && isset($product->category->parentCategory)) ? $product->category->parentCategory->id : '';
                $response['product']['parent_category']['name'] = (isset($product->category) && isset($product->category->parentCategory)) ? $product->category->parentCategory->name : '';
                $response['product']['sub_category']['id'] = ($product->category) ? $product->category->id : '';
                $response['product']['sub_category']['name'] = ($product->category) ? $product->category->name : '';
                $response['product']['name'] = $product->name;
                $response['product']['purchase_price'] = $product->purchase_price;
                $response['product']['selling_price'] = $product->selling_price;
                $response['product']['status'] = $product->status;
                $response['product']['created_at'] = date('d/m/Y h:i A', strtotime($product->created_at));

                $productImg = '';
                if ($product->image != '' && File::exists(public_path('uploads/product/'.$product->image))) {
                    $productImg = asset('uploads/product/'.$product->image);
                }

                $response['product']['image'] = $productImg;

                return sendResponse($response, 'Product data found.');
            } else {
                return sendError('Product not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'parent_category_id' => 'required',
                'sub_category_id' => 'required',
                'name' => 'required',
                'purchase_price' => 'required',
                'selling_price' => 'required',
            ];

            if ($request->has('image')) {
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:4096';
            }

            $messages = [
                'parent_category_id.required' => 'The parent category field is required.',
                'sub_category_id.required' => 'The sub category field is required.',
                'name.required' => 'The name field is required.',
                'purchase_price.required' => 'The purchase price field is required.',
                'selling_price.required' => 'The selling price field is required.',
                'image.required' => 'The image field is required.',
                'image.mimes' => 'Please insert image only.',
                'image.max' => 'Image should be less than 4 MB.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return sendError('Validation Error.', $validator->errors());
            } else {
                if ($request->has('product_id') && $request->product_id != '') {
                    $product = Product::where('id', $request->product_id)->first();
                    $action = 'updated';
                } else {
                    $product = new Product();
                    $action = 'added';
                }

                $product->user_id = Auth::id();
                $product->category_id = $request->sub_category_id;
                $product->name = $request->name;
                $product->purchase_price = $request->purchase_price;
                $product->selling_price = $request->selling_price;
                $product->details = $request->details;
                $product->status = ($request->has('status') && $request->status == 1) ? 1 : 0;

                if ($image = $request->file('image')) {
                    $productFolderPath = public_path('uploads/product/');
                    if (!File::isDirectory($productFolderPath)) {
                        File::makeDirectory($productFolderPath, 0777, true, true);
                    }

                    if ($product->image != '') {
                        $productImage = public_path('uploads/product/'.$product->image);

                        if (File::exists($productImage)) {
                            unlink($productImage);
                        }
                    }

                    $productImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                    $image->move($productFolderPath, $productImage);
                    $product->image = $productImage;
                }

                if ($product->save()) {
                    return sendResponse([], 'Product '.$action.' successfully.');
                } else {
                    return sendError('Product not '.$action.'.', []);
                }
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function destroy($productId) {
        try {
            $product = Product::find($productId);

            if ($product) {
                if ($product->image != '') {
                    $productImage = public_path('uploads/product/'.$product->image);

                    if (File::exists($productImage)) {
                        unlink($productImage);
                    }
                }

                $product->delete();

                return sendResponse([], 'Product deleted successfully.');
            } else {
                return sendError('Product not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function change_status($productId) {
        try {
            $product = Product::find($productId);

            if ($product) {
                $product->status = ($product->status == 1) ? 0 : 1;

                if ($product->save()) {
                    return sendResponse([], 'Status has been changed successfully.');
                } else {
                    return sendError('Status not update.', []);
                }
            } else {
                return sendError('Product not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }
}
