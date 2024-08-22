<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVarientValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class ProductController extends Controller
{
    public function __construct() {
        $this->middleware('permission:product-list', ['only' => ['index']]);
        $this->middleware('permission:product-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Product List';

            if (Auth::user()->can('product-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.product.create'),
                    'title' => 'Add Product',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Product List'
            );

            $parentCategories = Category::whereStatus(1)->whereNull('parent_id');
            $categories = Category::whereStatus(1)->whereNotNull('parent_id');

            if (!isSuperAdmin()) {
                $parentCategories->where('user_id', Auth::id());
                $categories->where('user_id', Auth::id());
            }

            $data['parent_categories'] = $parentCategories->get();
            $data['categories'] = $categories->get();


            if (isSuperAdmin()) {
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();
            }

            return view('admin.product.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $product = Product::query();

        if (!isSuperAdmin()) {
            $product->where('user_id', Auth::id());
        }

        if ($request->has('filter')) {
            if ($request->filter['fltStatus'] != '') {
                $product->where('status', $request->filter['fltStatus']);
            }

            if (isset($request->filter['user_id']) && $request->filter['user_id'] != '') {
                $product->where('user_id', $request->filter['user_id']);
            }

            if ($request->filter['parent_category_id'] != '') {
                $product->where('parent_category_id', $request->filter['parent_category_id']);
            }

            if ($request->filter['category_id'] != '') {
                $product->where('category_id', $request->filter['category_id']);
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $product->whereDate('created_at', $from_date);
                } else {
                    $product->whereBetween('created_at', [$from_date, $to_date]);
                }
            }
        }

        return DataTables::eloquent($product)
            ->addColumn('action', function($product) {
                $action = '';

                $action = '';
                if (Auth::user()->can('product-edit')) {
                    $action .= '<a href="'.route('admin.product.edit', $product->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('product-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.product.destroy').'" data-id="'.$product->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->addColumn('user', function($product) {
                return ($product->user) ? $product->user->name : '';
            })
            ->addColumn('parent_category', function($product) {
                return (isset($product->parentCategory) && isset($product->parentCategory)) ? $product->parentCategory->name : '';
            })
            ->addColumn('category', function($product) {
                return ($product->category) ? $product->category->name : '';
            })
            ->editColumn('purchase_price', function ($product) {
                return number_format($product->purchase_price, 2);
            })
            ->editColumn('selling_price', function ($product) {
                return number_format($product->selling_price, 2);
            })
            ->editColumn('image', function ($product) {
                if ($product->image != '' && File::exists(public_path('uploads/product/' . $product->image))) {
                    $image = '<img src="' . asset('uploads/product/' . $product->image) . '" id="product" class="rounded-circle header-profile-user" alt="Product Img">';
                } else {
                    $image = '-';
                }

                return $image;
            })
            ->editColumn('status', function ($product) {
                if (Auth::user()->can('product-edit')) {
                    $checkedAttr = $product->status == 1 ? 'checked' : '';
                    $status = '<div class="form-check form-switch form-switch-md mb-3" dir="ltr"> <input class="form-check-input js-switch" type="checkbox" data-id="' . $product->id . '" data-url="' . route('admin.product.change.status') . '" ' . $checkedAttr . '> </div>';
                } else {
                    $status = ($product->status == 1) ? 'Active' : 'InActive';
                }

                return $status;
            })
            ->editColumn('created_at', function($product) {
                return date('d/m/Y h:i A', strtotime($product->created_at));
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('name', $order);
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
            ->rawColumns(['action', 'image', 'status'])
            ->make(true);
    }

    public function create() {
        try {
            $data['page_title'] = 'Add Product';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('product-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.product.index'),
                    'title' => 'Product List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add Product'
            );

            $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                $q->where('id', '!=', 1);
            })->get();

            return view('admin.product.create', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'parent_category_id' => 'required',
                'name' => 'required',
                // 'purchase_price' => 'required',
                'selling_price' => 'required',
                'productVarientVals.*.price' => 'required'
            ];

            if (isSuperAdmin()) {
                $rules['user_id'] = 'required';
            }

            if ($request->has('image')) {
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:4096';
            }

            $messages = [
                'user_id.required' => 'The user field is required.',
                'parent_category_id.required' => 'The category field is required.',
                'name.required' => 'The name field is required.',
                // 'purchase_price.required' => 'The purchase price field is required.',
                'selling_price.required' => 'The selling price field is required.',
                'image.required' => 'The image field is required.',
                'image.mimes' => 'Please insert image only.',
                'image.max' => 'Image should be less than 4 MB.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if ($request->product_id != '') {
                    return redirect()->route('admin.product.edit', $request->product_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.product.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->product_id != '') {
                    $product = Product::where('id', $request->product_id)->first();
                    $action = 'updated';
                } else {
                    $product = new Product();
                    $action = 'added';
                }

                $product->user_id = isSuperAdmin() ? $request->user_id : Auth::id();
                $product->parent_category_id = $request->parent_category_id;
                $product->category_id = $request->category_id;
                $product->name = $request->name;
                $product->purchase_price = ($request->purchase_price != null) ? $request->purchase_price : 0;
                $product->selling_price = ($request->selling_price != null) ? $request->selling_price : 0;
                $product->details = $request->details;
                $product->status = ($request->has('status') && $request->status == 'on') ? 1 : 0;

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
                    if ($request->has('productVarientVals') && count($request->productVarientVals) > 0 && $request->productVarientVals[0]['price'] != '') {
                        if ($request->product_id != '') {
                            ProductVarientValue::where('product_id', $request->product_id)->delete();
                        }

                        foreach ($request->productVarientVals as $productVarientVal) {
                            $productVarient = new ProductVarientValue();
                            $productVarient->product_id = $product->id;
                            $productVarient->category_varient_id = $productVarientVal['varientId'];
                            $productVarient->price = $productVarientVal['price'];
                            $productVarient->save();
                        }
                    }

                    Session::flash('alert-message', "Product ".$action." successfully.");
                    Session::flash('alert-class','success');

                    return redirect()->route('admin.product.index');
                } else {
                    Session::flash('alert-message', "Product not ".$action.".");
                    Session::flash('alert-class','error');

                    if ($request->product_id != '') {
                        return redirect()->route('admin.product.edit', $request->product_id);
                    } else {
                        return redirect()->route('admin.product.create');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','error');

            if ($request->product_id != '') {
                return redirect()->route('admin.product.edit', $request->product_id);
            } else {
                return redirect()->route('admin.product.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit Product';
            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('product-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.product.index'),
                    'title' => 'Product List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit Product'
            );

            $product = Product::find($id);

            if ($product) {
                $data['product'] = $product;
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();

                return view('admin.product.create', $data);
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
                $product = Product::where('id', $request->id)->first();

                if ($product) {
                    if ($product->image != '') {
                        $productImage = public_path('uploads/product/'.$product->image);

                        if (File::exists($productImage)) {
                            unlink($productImage);
                        }
                    }

                    $product->delete();

                    $return['success'] = true;
                    $return['message'] = "Product deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "Product not found.";
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
                $product = Product::find($request->id);
                $product->status = $request->status;

                if ($product->save()) {
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
