<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;

use App\Http\Controllers\Admin\AjaxController;

Route::get('/', function () {
    return redirect()->route('admin.index');
});

Auth::routes(['verify' => true]);

Route::controller(AjaxController::class)->group(function () {
    Route::group(['as' => 'ajax.', 'prefix' => 'ajax'], function() {
        Route::get('/get-parent-category', 'getParentCategory')->name('get_parent_category');
        Route::get('/get-sub-category', 'getSubCategory')->name('get_sub_category');
        Route::get('/get-product', 'getProduct')->name('get_product');
        Route::post('/get-category-varient', 'getCategoryVarients')->name('get_category_varients');
        Route::get('/get-inventory-products', 'getInventoryProducts')->name('get_inventory_products');
    });
});

Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [AdminHomeController::class, 'index'])->name('index');

    Route::controller(ProfileController::class)->group(function () {
        Route::group(['as' => 'profile.', 'prefix' => 'profile'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/update', 'update')->name('update');
            Route::post('/password/update', 'password_update')->name('password.update');
            Route::post('/check_password', 'check_password')->name('check.password');
        });
    });

    Route::controller(PermissionController::class)->group(function () {
        Route::group(['as' => 'permission.', 'prefix' => 'permission'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
        });
    });

    Route::controller(RoleController::class)->group(function () {
        Route::group(['as' => 'role.', 'prefix' => 'role'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
        });
    });

    Route::controller(AdminUserController::class)->group(function () {
        Route::group(['as' => 'user.', 'prefix' => 'user'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
            Route::post('/exists', 'exists')->name('exists');
        });
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::group(['as' => 'category.', 'prefix' => 'category'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
        });
    });

    Route::controller(ProductController::class)->group(function () {
        Route::group(['as' => 'product.', 'prefix' => 'product'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
        });
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::group(['as' => 'inventory.', 'prefix' => 'inventory'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
        });
    });

    Route::controller(OrderController::class)->group(function () {
        Route::group(['as' => 'order.', 'prefix' => 'order'], function() {
            Route::get('/', 'index')->name('index');
            Route::post('/datatable', 'datatable')->name('datatable');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/destroy', 'destroy')->name('destroy');
            Route::post('/change_status', 'change_status')->name('change.status');
            Route::post('/products-datatable', 'productsDatatable')->name('products.datatable');
        });
    });
});
