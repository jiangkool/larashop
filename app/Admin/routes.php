<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('users','UserController');
    $router->resource('products','ProductController');
    $router->resource('orders', 'OrdersController');

    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.index');

    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship');

    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund');
});
