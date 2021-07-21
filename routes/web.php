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

// DASHBOARD AND HOME
Route::get('/dashboard', 'OrderController@dashboard')->middleware(['auth.shop', 'billable'])->name('home');
Route::get('/order', 'OrderController@dashboard')->middleware(['auth.shop', 'billable'])->name('home');
Route::get('/', function () {
    // return view('welcome');
    return Redirect::to('http://orderly.webflow.io');
})->name('home');

// SETTINGS
Route::get('/settings', 'SettingController@create')->middleware(['auth.shop']);
Route::post('/settings/store', 'SettingController@store')->middleware(['auth.shop']);
Route::get('/settings/edit', 'SettingController@edit')->middleware(['auth.shop']);
Route::post('/settings/update/{id}', 'SettingController@update')->middleware(['auth.shop']);


// ORDER
Route::get('/order/charge-customer', 'OrderController@adminLink')->middleware('auth.shop');
Route::post('/order/{order}/customer/{customer_id}/card/{card}/amount/{amount}', 'OrderController@chargeCustomerPaymentProfile');
Route::get('/order/{order_id}/status', 'OrderController@checkTransactionStatus');

// JOBS
Route::get('/transaction/check/', 'OrderController@checkTransactionSatusJob');

// CUSTOMER
Route::resource('customer', 'CustomerController')->middleware(['auth.shop']);
Route::get('customer/create/prefilled/{id}', 'CustomerController@createPrefilled')->middleware(['auth.shop']);

// CARD
Route::get('card/create/customer/{id}', 'CardController@create')->middleware(['auth.shop']);
Route::post('card/store/customer/{id}', 'CardController@store')->middleware(['auth.shop']);
Route::get('card/{card}/delete/', 'CardController@destroy')->middleware(['auth.shop']);

Route::post('log', 'OrderController@log');


// SUPPORT