<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryVarient;
use App\Models\Category;
use App\Models\User;
use DataTables;
use Validator;
use Session;
use File;
use Auth;

class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware('permission:category-list', ['only' => ['index']]);
        $this->middleware('permission:category-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:category-edit', ['only' => ['edit', 'store', 'change_status']]);
        $this->middleware('permission:category-delete', ['only' => ['destroy']]);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Category List';

            if (Auth::user()->can('category-add')) {
                $data['btnadd'][] = array(
                    'link' => route('admin.category.create'),
                    'title' => 'Add Category',
                );
            }

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Category List'
            );

            $categories = Category::whereStatus(1)->whereNull('parent_id');

            if (!isSuperAdmin()) {
                $categories->where('user_id', Auth::id());
            }

            $data['parent_categories'] = $categories->get();

            if (isSuperAdmin()) {
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();
            }

            return view('admin.category.index', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function datatable(Request $request) {
        $category = Category::query();

        if (!isSuperAdmin()) {
            $category->where('user_id', Auth::id());
        }

        if ($request->has('filter')) {
            if ($request->filter['fltStatus'] != '') {
                $category->where('status', $request->filter['fltStatus']);
            }

            if (isset($request->filter['user_id']) && $request->filter['user_id'] != '') {
                $category->where('user_id', $request->filter['user_id']);
            }

            if ($request->filter['parent_id'] != '') {
                $category->where('parent_id', $request->filter['parent_id']);
            }

            if ($request->filter['date'] != '') {
                $date = explode(' - ', $request->filter['date']);
                $from_date = date('Y-m-d', strtotime($date[0]));
                $to_date = date('Y-m-d', strtotime($date[1]));

                if ($from_date == $to_date) {
                    $category->whereDate('created_at', $from_date);
                } else {
                    $category->whereBetween('created_at', [$from_date, $to_date]);
                }
            }
        }

        return DataTables::eloquent($category)
            ->addColumn('action', function($category) {
                $action = '';

                $action = '';
                if (Auth::user()->can('category-edit')) {
                    $action .= '<a href="'.route('admin.category.edit', $category->id).'" class="btn btn-outline-secondary btn-sm" title="Edit"><i class="fas fa-pencil-alt"></i></a>&nbsp;';
                }

                if (Auth::user()->can('category-delete')) {
                    $action .= '<a class="btn btn-outline-secondary btn-sm btnDelete" data-url="'.route('admin.category.destroy').'" data-id="'.$category->id.'" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }

                return $action;
            })
            ->addColumn('user', function($category) {
                return ($category->user) ? $category->user->name : '';
            })
            ->addColumn('parent_category', function($category) {
                return ($category->parentCategory) ? $category->parentCategory->name : '';
            })
            ->editColumn('image', function ($category) {
                if ($category->image != '' && File::exists(public_path('uploads/category/' . $category->image))) {
                    $image = '<img src="' . asset('uploads/category/' . $category->image) . '" id="category" class="rounded-circle header-profile-user" alt="Category Img">';
                } else {
                    $image = '-';
                }

                return $image;
            })
            ->editColumn('status', function ($category) {
                if (Auth::user()->can('category-edit')) {
                    $checkedAttr = $category->status == 1 ? 'checked' : '';
                    $status = '<div class="form-check form-switch form-switch-md mb-3" dir="ltr"> <input class="form-check-input js-switch" type="checkbox" data-id="' . $category->id . '" data-url="' . route('admin.category.change.status') . '" ' . $checkedAttr . '> </div>';
                } else {
                    $status = ($category->status == 1) ? 'Active' : 'InActive';
                }

                return $status;
            })
            ->editColumn('created_at', function($category) {
                return date('d/m/Y h:i A', strtotime($category->created_at));
            })
            ->orderColumn('id', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            // ->orderColumn('parent_category', function ($query, $order) {
            //     $query->orWhereHas('parentCategory', function ($q) use ($order) {
            //         $q->orderBy('name', $order);
            //     });
            // })
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('name', $order);
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
            $data['page_title'] = 'Add Category';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('category-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.category.index'),
                    'title' => 'Category List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Add Category'
            );

            $data['parent_categories'] = Category::whereNull('parent_id')->whereStatus(1)->get();

            $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                $q->where('id', '!=', 1);
            })->get();

            return view('admin.category.create', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'name' => 'required',
            ];

            if (isSuperAdmin()) {
                $rules['user_id'] = 'required';
            }

            if ($request->has('image')) {
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:4096';
            }

            $messages = [
                'name.required' => 'The name field is required.',
                'image.required' => 'The image field is required.',
                'image.mimes' => 'Please insert image only.',
                'image.max' => 'Image should be less than 4 MB.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                if ($request->category_id != '') {
                    return redirect()->route('admin.category.edit', $request->category_id)
                                ->withErrors($validator)
                                ->withInput();
                } else {
                    return redirect()->route('admin.category.create')
                                ->withErrors($validator)
                                ->withInput();
                }
            } else {
                if ($request->category_id != '') {
                    $category = Category::where('id', $request->category_id)->first();
                    $action = 'updated';
                } else {
                    $category = new Category();
                    $action = 'added';
                }

                $category->name = $request->name;
                $category->user_id = isSuperAdmin() ? $request->user_id : Auth::id();
                $category->parent_id = (isset($request->parent_id) && $request->parent_id != '') ? $request->parent_id : null;
                $category->details = $request->details;
                $category->notes = $request->notes;
                $category->status = ($request->has('status') && $request->status == 'on') ? 1 : 0;

                if ($image = $request->file('image')) {
                    $categoryFolderPath = public_path('uploads/category/');
                    if (!File::isDirectory($categoryFolderPath)) {
                        File::makeDirectory($categoryFolderPath, 0777, true, true);
                    }

                    if ($category->image != '') {
                        $categoryImage = public_path('uploads/category/'.$category->image);

                        if (File::exists($categoryImage)) {
                            unlink($categoryImage);
                        }
                    }

                    $categoryImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
                    $image->move($categoryFolderPath, $categoryImage);
                    $category->image = $categoryImage;
                }

                if ($category->save()) {
                    if (count($request->categoryVarients) > 0 && $request->categoryVarients[0]['name'] != '') {
                        if ($request->category_id != '') {
                            CategoryVarient::where('category_id', $request->category_id)->delete();
                        }

                        foreach ($request->categoryVarients as $varient) {
                            $categoryVarient = new CategoryVarient();
                            $categoryVarient->category_id = $category->id;
                            $categoryVarient->name = $varient['name'];
                            $categoryVarient->save();
                        }
                    }

                    Session::flash('alert-message', "Category ".$action." successfully.");
                    Session::flash('alert-class','success');

                    return redirect()->route('admin.category.index');
                } else {
                    Session::flash('alert-message', "Category not ".$action.".");
                    Session::flash('alert-class','error');

                    if ($request->category_id != '') {
                        return redirect()->route('admin.category.edit', $request->category_id);
                    } else {
                        return redirect()->route('admin.category.create');
                    }
                }
            }
        } catch (\Exception $e) {
            Session::flash('alert-message', $e->getMessage());
            Session::flash('alert-class','error');

            if ($request->category_id != '') {
                return redirect()->route('admin.category.edit', $request->category_id);
            } else {
                return redirect()->route('admin.category.create');
            }
        }
    }

    public function edit($id) {
        try {
            $data['page_title'] = 'Edit Category';
            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            if (Auth::user()->can('category-list')) {
                $data['breadcrumb'][] = array(
                    'link' => route('admin.category.index'),
                    'title' => 'Category List'
                );
            }

            $data['breadcrumb'][] = array(
                'title' => 'Edit Category'
            );

            $category = Category::find($id);

            if ($category) {
                $data['category'] = $category;
                $data['parent_categories'] = Category::where('id', '!=', $id)->whereNull('parent_id')->whereStatus(1)->get();
                $data['users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                    $q->where('id', '!=', 1);
                })->get();

                return view('admin.category.create', $data);
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
                $category = Category::where('id', $request->id)->first();

                if ($category) {
                    if ($category->image != '') {
                        $categoryImage = public_path('uploads/category/'.$category->image);

                        if (File::exists($categoryImage)) {
                            unlink($categoryImage);
                        }
                    }

                    $category->delete();

                    $return['success'] = true;
                    $return['message'] = "Category deleted successfully.";
                } else {
                    $return['success'] = false;
                    $return['message'] = "Category not found.";
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
                $category = Category::find($request->id);
                $category->status = $request->status;

                if ($category->save()) {
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
