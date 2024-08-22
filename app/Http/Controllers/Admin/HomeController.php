<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class HomeController extends Controller
{
    public function __construct() {
        $this->middleware(['auth', 'verified']);
    }

    public function index() {
        try {
            $data = [];
            $data['page_title'] = 'Dashboard';

            $data['breadcrumb'][] = array(
                'link' => route('admin.index'),
                'title' => 'Dashboard'
            );

            $data['breadcrumb'][] = array(
                'title' => 'Dashboard'
            );

            $data['total_users'] = User::whereStatus(1)->whereHas('roles', function ($q) {
                $q->where('id', '!=', 1);
            })->count();

            $data['total_categories'] = Category::whereStatus(1)->count();
            $data['total_products'] = Product::whereStatus(1)->count();

            return view('admin.dashboard', $data);
        } catch (\Exception $e) {
            return abort(404);
        }
    }
}
