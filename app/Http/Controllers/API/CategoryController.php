<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Validator;
use Session;
use File;
use Auth;

class CategoryController extends Controller
{
    public function category_list(Request $request) {
        try {
            $categories = Category::where('user_id', Auth::id());

            if ($request->has('name') && $request->name != '') {
                $categories = $categories->where('name', 'like', '%'.$request->name.'%');
            }

            if ($request->has('parent_category_id') && $request->parent_category_id != '') {
                $categories = $categories->where('parent_id', $request->parent_category_id);
            }

            if ($request->has('status') && $request->status != '') {
                $categories = $categories->whereStatus($request->status);
            }

            if ($request->has('from_date') && $request->from_date != '' && $request->has('to_date') && $request->to_date != '') {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date = date('Y-m-d', strtotime($request->to_date));

                if ($from_date == $to_date) {
                    $categories->whereDate('created_at', $from_date);
                } else {
                    $categories->whereBetween('created_at', [$from_date, $to_date]);
                }
            }

            $limit = $request->input('per_page', 10);
            $offset = ($request->input('page', 1) - 1) * $limit;
            $totalRecords = $categories->count();

            $categories = $categories->offset($offset)->limit($limit)->get();

            if ($categories) {
                $response['categories'] = [];

                foreach ($categories as $key => $category) {
                    $response['categories'][$key]['id'] = $category->id;
                    $response['categories'][$key]['user_id'] = Auth::id();
                    $response['categories'][$key]['parent_category'] = ($category->parentCategory) ? $category->parentCategory->name : '';
                    $response['categories'][$key]['name'] = $category->name;
                    $response['categories'][$key]['status'] = $category->status;
                    $response['categories'][$key]['created_at'] = date('d/m/Y h:i A', strtotime($category->created_at));

                    $categoryImg = '';
                    if ($category->image != '' && File::exists(public_path('uploads/category/'.$category->image))) {
                        $categoryImg = asset('uploads/category/'.$category->image);
                    }

                    $response['categories'][$key]['image'] = $categoryImg;
                }

                $response['totalRecords'] = $totalRecords;
                $response['offset'] = (int)$request->input('page', 1);

                return sendResponse($response, 'Category data found.');
            } else {
                return sendError('Categories not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function details($categoryId) {
        try {
            $category = Category::find($categoryId);

            if ($category) {
                $response['category']['id'] = $category->id;

                if ($category->parentCategory) {
                    $response['category']['parent_category']['id'] = $category->parentCategory->id;
                    $response['category']['parent_category']['name'] = $category->parentCategory->name;
                } else {
                    $response['category']['parent_category'] = null;
                }

                $response['category']['name'] = $category->name;
                $response['category']['status'] = $category->status;
                $response['category']['created_at'] = date('d/m/Y h:i A', strtotime($category->created_at));

                $categoryImg = '';
                if ($category->image != '' && File::exists(public_path('uploads/category/'.$category->image))) {
                    $categoryImg = asset('uploads/category/'.$category->image);
                }

                $response['category']['image'] = $categoryImg;

                return sendResponse($response, 'Category data found.');
            } else {
                return sendError('Category not found.', []);
            }
        } catch (\Exception $e) {
            return sendError('Something went wrong.', $e->getMessage());
        }
    }

    public function store(Request $request) {
        try {
            $rules = [
                'name' => 'required',
            ];

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
                return sendError('Validation Error.', $validator->errors());
            } else {
                if ($request->has('category_id') && $request->category_id != '') {
                    $category = Category::where('id', $request->category_id)->first();
                    $action = 'updated';
                } else {
                    $category = new Category();
                    $action = 'added';
                }

                $category->user_id = Auth::id();
                $category->name = $request->name;
                $category->parent_id = $request->parent_category_id;
                $category->details = $request->details;
                $category->notes = $request->notes;
                $category->status = ($request->has('status') && $request->status == 1) ? 1 : 0;

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
                    return sendResponse([], 'Category '.$action.' successfully.');
                } else {
                    return sendError('Category not '.$action.'.', []);
                }
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function destroy($categoryId) {
        try {
            $category = Category::find($categoryId);

            if ($category) {
                if ($category->image != '') {
                    $categoryImage = public_path('uploads/category/'.$category->image);

                    if (File::exists($categoryImage)) {
                        unlink($categoryImage);
                    }
                }

                $category->delete();

                return sendResponse([], 'Category deleted successfully.');
            } else {
                return sendError('Category not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }

    public function change_status($categoryId) {
        try {
            $category = Category::find($categoryId);

            if ($category) {
                $category->status = ($category->status == 1) ? 0 : 1;

                if ($category->save()) {
                    return sendResponse([], 'Status has been changed successfully.');
                } else {
                    return sendError('Status not update.', []);
                }
            } else {
                return sendError('Category not found.', []);
            }
        } catch (\Exception $e) {
            return sendError($e->getMessage(), []);
        }
    }
}
