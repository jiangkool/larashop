<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',['uses'=>'PagesController@root','middleware'=>'verified','as'=>'root']);

Auth::routes(['verify' => true]);

// Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['auth','verified']], function() {

    Route::get('user_addresses', 'UserAddressController@index')->name('user_addresses.index');
    Route::get('user_addresses/create', 'UserAddressController@create')->name('user_addresses.create');
    Route::post('user_addresses', 'UserAddressController@store')->name('user_addresses.store');
    Route::get('user_addresses/{user_address}/edit', 'UserAddressController@edit')->name('user_addresses.edit');
    Route::put('user_addresses/{user_address}', 'UserAddressController@update')->name('user_addresses.update');
    Route::delete('user_addresses/{user_address}', 'UserAddressController@destroy')->name('user_addresses.destroy');

    //收藏 & 取消收藏
    Route::post('products/{product}/favorite', 'ProductController@favor')->name('products.favor');
    Route::delete('products/{product}/favorite', 'ProductController@disfavor')->name('products.disfavor');

    Route::get('favorites','ProductController@favorites')->name('products.favorites');

    // 购物车模块
    Route::post('cart', 'CartController@add')->name('cart.add');
    Route::get('cart', 'CartController@index')->name('cart.index');
    Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');

    //订单模块
    Route::post('orders', 'OrdersController@store')->name('orders.store');

});

Route::get('/products', 'ProductController@index')->name('products.index');
Route::get('/products/{product}', 'ProductController@show')->name('products.show');