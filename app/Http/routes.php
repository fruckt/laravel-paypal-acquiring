<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', [
    'as' => 'home',
    'uses' => 'HomeController@index',
]);

Route::post('/order', [
    'as' => 'acquiring.order',
    'uses' => 'AcquiringController@postOrder'
]);

Route::get('/payment', [
    'as' => 'acquiring.payment',
    'uses' => 'AcquiringController@getPayment'
]);

Route::post('/answer', [
    'as' => 'acquiring.answer',
    'uses' => 'AcquiringController@postAnswer'
]);

Route::get('/status', [
    'as' => 'acquiring.status',
    'uses' => 'AcquiringController@getStatus'
]);

