<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Inventory;
use Validator;
use File;

class ApiController extends Controller
{
    public function category_list(Request $request) {
        try {
            $categories = Category::where('user_id', Auth::id())->whereStatus(1);

            if ($request->has('name') && $request->name != '') {
                $categories = $categories->where('name', 'like', '%'.$request->name.'%');
            }

            $categories = $categories->get();

            if ($categories) {
                $response['categories'] = [];

                foreach ($categories as $key => $category) {
                    $response['categories'][$key]['id'] = $category->id;
                    $response['categories'][$key]['name'] = $category->name;
                }

                return sendResponse($response, 'Category data found.');
            } else {
                return sendError('Category not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function parent_category_list(Request $request) {
        try {
            $categories = Category::where('user_id', Auth::id())->whereStatus(1)->whereNull('parent_id');

            if ($request->has('name') && $request->name != '') {
                $categories = $categories->where('name', 'like', '%'.$request->name.'%');
            }

            $categories = $categories->get();

            if ($categories) {
                $response['categories'] = [];

                foreach ($categories as $key => $category) {
                    $response['categories'][$key]['id'] = $category->id;
                    $response['categories'][$key]['name'] = $category->name;
                }

                return sendResponse($response, 'Parent Category data found.');
            } else {
                return sendError('Parent Category not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function sub_category_list(Request $request) {
        try {
            $categories = Category::where('user_id', Auth::id())->whereStatus(1)->whereNotNull('parent_id')->where('parent_id', $request->parent_category_id);

            if ($request->has('name') && $request->name != '') {
                $categories = $categories->where('name', 'like', '%'.$request->name.'%');
            }

            $categories = $categories->get();

            if ($categories) {
                $response['categories'] = [];

                foreach ($categories as $key => $category) {
                    $response['categories'][$key]['id'] = $category->id;
                    $response['categories'][$key]['name'] = $category->name;
                }

                return sendResponse($response, 'Sub Category data found.');
            } else {
                return sendError('Sub Category not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function product_list(Request $request) {
        try {
            $inventories = Inventory::with('product.parentCategory')->where('user_id', Auth::id())->whereStatus(1);

            if ($request->has('category_ids') && $request->category_ids != '') {
                $categoryIds = explode(',', $request->category_ids);

                $inventories->whereHas('product', function($q) use ($categoryIds) {
                    $q->where(function($q1) use ($categoryIds) {
                        $q1->whereIn('parent_category_id', $categoryIds)
                            ->orWhereIn('category_id', $categoryIds);
                    });
                });
            }

            if ($request->has('name') && $request->name != '') {
                $productName = $request->name;

                $inventories->whereHas('product', function($q) use ($productName) {
                    $q->where('name', 'like', '%'.$productName.'%');
                });
            }

            $inventories = $inventories->get()->groupBy(function($q) {
                return $q->product->parentCategory->name;
            });

            if ($inventories) {
                $response = $inventories->map(function($invs, $category) {
                    return [
                        'category' => $category,
                        'products' => $invs->map(function($inv) {
                            $productImg = '';
                            if (isset($inv->product) && $inv->product->image != '' && File::exists(public_path('uploads/product/'.$inv->product->image))) {
                                $productImg = asset('uploads/product/'.$inv->product->image);
                            }

                            if ($inv->product) {
                                return [
                                    'id' => $inv->product->id,
                                    'inventory_id' => $inv->id,
                                    'category' => ($inv->product->category) ? $inv->product->category->name : '',
                                    'name' => $inv->product->name,
                                    'price' => number_format($inv->selling_price, 2),
                                    'quantity' => $inv->quantity,
                                    'image' => $productImg,
                                ];
                            }
                        })
                    ];
                })->values();

                return sendResponse($response, 'Product data found.');
            } else {
                return sendError('Product not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }
}
