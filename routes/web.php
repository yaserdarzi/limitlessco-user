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


Route::get('/', function () {
    return view('welcome');
});

//Route::get('/test/{password}', function ($password) {
//    dd(\Illuminate\Support\Facades\Hash::make('202020'));
//});

Route::get('/ticket/{shopping_id}', function ($shopping_id) {
    return view('ticket/downloadTicketPDF', ['shopping_id' => $shopping_id]);
});

Route::get('/save/ticket/{shopping_id}', function ($shopping_id) {
    return view('ticket/saveTicketPDF', ['shopping_id' => $shopping_id]);
});