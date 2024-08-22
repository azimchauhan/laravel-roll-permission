<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\OrderController;

Route::group(['as' => 'user.', 'prefix' => 'user'], function () {
    Route::controller(ApiController::class)->group(function () {
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/category-list', 'category_list');
            Route::get('/parent-category-list', 'parent_category_list');
            Route::get('/sub-category-list', 'sub_category_list');
            Route::get('/product-list', 'product_list');
        });
    });

    Route::controller(AuthController::class)->group(function () {
        Route::group(['prefix' => 'auth'], function() {
            Route::post('/login', 'login');
            Route::post('/change-password', 'change_password')->middleware('auth:api');
            Route::post('/logout', 'logout')->middleware('auth:api');
        });
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::group(['prefix' => 'category', 'middleware' => 'auth:api'], function() {
            Route::get('/', 'category_list');
            Route::get('/details/{id}', 'details');
            Route::post('/store', 'store');
            Route::delete('/delete/{id}', 'destroy');
            Route::put('/change-status/{id}', 'change_status');
        });
    });

    Route::controller(ProductController::class)->group(function () {
        Route::group(['prefix' => 'product', 'middleware' => 'auth:api'], function() {
            Route::get('/', 'product_list');
            Route::get('/details/{id}', 'details');
            Route::post('/store', 'store');
            Route::delete('/delete/{id}', 'destroy');
            Route::put('/change-status/{id}', 'change_status');
        });
    });

    Route::controller(InventoryController::class)->group(function () {
        Route::group(['prefix' => 'inventory', 'middleware' => 'auth:api'], function() {
            Route::get('/', 'inventory_list');
            Route::get('/details/{id}', 'details');
            Route::post('/store', 'store');
            Route::delete('/delete/{id}', 'destroy');
            Route::put('/change-status/{id}', 'change_status');
        });
    });

    Route::controller(OrderController::class)->group(function () {
        Route::group(['prefix' => 'order', 'middleware' => 'auth:api'], function() {
            Route::get('/', 'order_list');
            Route::get('/details/{id}', 'details');
            Route::post('/store', 'store');
            Route::delete('/delete/{id}', 'destroy');
            Route::put('/change-status/{id}', 'change_status');
        });
    });
});
