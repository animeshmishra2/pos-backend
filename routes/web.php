<?php

use Illuminate\Support\Facades\Route;

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


Route::get('excel-import','FileController@importProductView');
 Route::post('excel-import','FileController@brandImport')->name('excelImport');
//  Route::post('excel-import','FileController@productImport')->name('excelImport');
// Route::get('file-import','FileController@importView');
// Route::post('file-import','FileController@import');


// Route::get('sync-brand','FileController@syncBrands');
// Route::get('sync-cat','FileController@syncCategory');
// Route::get('sync-sub-cat','FileController@syncSubCategory');
// Route::get('sync-sub-sub-cat','FileController@syncSubSubCategory');