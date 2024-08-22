<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryVarient;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;

class AjaxController extends Controller
{
    public function getParentCategory(Request $request) {
        $query = Category::whereNull('parent_id')->where('user_id', $request->userId);

        if ($request->search) {
            $query = $query->where('name', 'like', '%' .$request->search. '%');
        }

        $query = $query->simplePaginate(50);

        $no = 0;
        $data = array();

        foreach ($query as $item) {
            $data[$no]['id'] = $item->id;
            $data[$no]['text'] = $item->name;
            $no++;
        }

        $page = true;

        if (empty($query->nextPageUrl())) {
            $page = false;
        }

        return ['results' => $data, 'pagination' => ['more' => $page]];
    }

    public function getSubCategory(Request $request) {
        $data = array();
        $page = false;

        if ($request->has('categoryId')) {
            $query = Category::where('parent_id', $request->categoryId);

            if ($request->search) {
                $query = $query->where('name', 'like', '%' .$request->search. '%');
            }

            $query = $query->simplePaginate(50);

            $no = 0;

            foreach ($query as $item) {
                $data[$no]['id'] = $item->id;
                $data[$no]['text'] = $item->name;
                $no++;
            }

            $page = false;

            if (empty($query->nextPageUrl())) {
                $page = false;
            }
        }

        return ['results' => $data, 'pagination' => ['more' => $page]];
    }

    public function getProduct(Request $request) {
        $query = Product::whereStatus(1)->where('user_id', $request->userId);

        if ($request->search) {
            $query = $query->where('name', 'like', '%' .$request->search. '%');
        }

        $query = $query->simplePaginate(50);

        $no = 0;
        $data = array();

        foreach ($query as $item) {
            $data[$no]['id'] = $item->id;
            $data[$no]['text'] = $item->name;
            $no++;
        }

        $page = true;

        if (empty($query->nextPageUrl())) {
            $page = false;
        }

        return ['results' => $data, 'pagination' => ['more' => $page]];
    }

    public function getCategoryVarients(Request $request) {
        if ($request->ajax()) {
            try {
                $categoryVarients = CategoryVarient::where('category_id', $request->categoryId)->get();

                return response()->json([
                    'success' => true,
                    'categoryVarients' => $categoryVarients
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!'
            ]);
        }
    }

    public function getInventoryProducts(Request $request) {
        $query = Inventory::whereStatus(1)->where('user_id', $request->userId);

        if ($request->search) {
            $query = $query->where('name', 'like', '%' .$request->search. '%');
        }

        $query = $query->simplePaginate(50);

        $no = 0;
        $data = array();

        foreach ($query as $item) {
            if ($item->product) {
                $data[$no]['id'] = $item->product->id;
                $data[$no]['text'] = $item->product->name;
                $data[$no]['inventoryId'] = $item->id;
                $data[$no]['sellingPrice'] = $item->selling_price;
                $data[$no]['maxQuantity'] = $item->quantity;
                $no++;
            }
        }

        $page = true;

        if (empty($query->nextPageUrl())) {
            $page = false;
        }

        return ['results' => $data, 'pagination' => ['more' => $page]];
    }
}
