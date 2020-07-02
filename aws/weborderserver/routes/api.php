<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => ['api']], function () {
    // 本社データ連携API
    Route::post('/weborderdatareceive', 'WebOrderDataReceiveController@receive');
    Route::post('/weborderdatasend', 'WebOrderDataSendController@send');
    Route::post('/generateorderid', 'WebOrderDataSendController@generateOrderId');
    Route::post('/generateorderinfoid', 'WebOrderDataSendController@generateOrderInfoId');
    Route::get('/ping', 'ConnectionTestController@index');
});
