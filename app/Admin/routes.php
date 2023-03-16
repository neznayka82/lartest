<?php

Route::get('/products/getbyid', \App\Http\Controllers\ProductController::class . "@getbyid" );
Route::get('/products/getall', \App\Http\Controllers\ProductController::class . "@getall" );

Route::get('/managers/getall', \App\Http\Controllers\ManagerController::class . "@getall" );

Route::get('/orders/getbyid', \App\Http\Controllers\OrdersController::class . "@getbyid" );
Route::post('/orders/create', \App\Http\Controllers\OrdersController::class . "@create" );
Route::post('/orders/update', \App\Http\Controllers\OrdersController::class . "@update" );

Route::get('report', [
    'as' => 'order.report',
    'uses' => '\App\Http\Controllers\OrdersController@report',
]);

//Route::get('/orders/report/report', ['as' => 'admin.orders.report', \App\Http\Controllers\OrdersController::class . "@index"]);
