<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['api']], function () {

    // 情報取得API
    Route::get('/shohinmasterfill', 'ApiController@ShohinMasterFill');
    Route::get('/kakushumasterfill', 'ApiController@KakushuMasterFill');
    Route::post('/gettokuisakitanka', 'ApiController@getTokuisakiTanka');
    Route::post('/getcourseinfo', 'ApiController@getCourseInfo');
    Route::post('/getsaledata', 'ApiController@getSaleData');
    Route::post('/getcoursesaledata', 'ApiController@getCourseSaleData');
    Route::post('/getsaledivideddata', 'ApiController@getSaleDividedData');
    Route::post('/getcoursesaledivideddata', 'ApiController@getCourseSaleDividedData');
    Route::post('/getplansaledata', 'ApiController@getPlanSaleData');
    Route::post('/getcourseplansaledata', 'ApiController@getCoursePlanSaleData');
    Route::post('/getplansaledivideddata', 'ApiController@getPlanSaleDividedData');
    Route::post('/getmoveinfo', 'ApiController@getMoveInfo');
    Route::post('/getpayinfo', 'ApiController@getPayInfo');
    Route::post('/getmobilemessage', 'ApiController@getMobileMessage');
    Route::post('/getmainshohin', 'ApiController@getMainShohin');
    Route::post('/getresponsibledata', 'ApiController@getResponsibleData');
    Route::post('/getcollectioninputdata', 'ApiController@getCollectionInputData');


    // 情報登録API
    Route::put('/setcourseinfo', 'ApiSetController@setCourseInfo');
    Route::put('/setsaledata', 'ApiSetController@setSaleData');
    Route::put('/setcoursesaledata', 'ApiSetController@setCourseSaleData');
    Route::put('/setchumoninfotojisseki', 'ApiSetController@setChumonInfoToJisseki');
    Route::put('/setsaledivideddata', 'ApiSetController@setSaleDividedData');
    Route::put('/setcoursesaledivideddata', 'ApiSetController@setCourseSaleDividedData');
    Route::put('/setplansaledata', 'ApiSetController@setPlanSaleData');
    Route::put('/setcourseplansaledata', 'ApiSetController@setCoursePlanSaleData');
    Route::put('/applycourseplansaledataprevjisseki', 'ApiSetController@applyCoursePlanSaleDataPrevJisseki');
    Route::put('/setplansaledivideddata', 'ApiSetController@setPlanSaleDividedData');
    Route::put('/setmoveinfo', 'ApiSetController@setMoveInfo');
    Route::put('/setresponsibledata', 'ApiSetController@setResponsibleData');

    // 情報削除API
    Route::delete('/deletecourseinfo', 'ApiDeleteController@deleteCourseInfo');

    // 本社モバイルデータ連携API
    Route::post('/mobiledatareceive', 'MobileDataReceiveController@receive');
    Route::post('/mobiledatasend', 'MobileDataSendController@send');
    Route::get('/ping', 'ConnectionTestController@index');
});
