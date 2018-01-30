<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

//Route::options('*', function () {
//    $response = Response::make('');
//    $response->header('Access-Control-Allow-Origin', '*');
//    $response->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
//    $response->header('Access-Control-Allow-Headers', 'X-Requested-With');
//    return $response;
//});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::group(['middleware' => 'cors'], function () {
Route::post('/register', 'Auth\RegisterController@register');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout');
Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::post('user/reset-password', 'Auth\ResetPasswordController@userPasswordReset');

Route::group(['middleware' => 'auth:api'], function () {

    /**Facebook Socialite*/
    Route::get('login/facebook', 'Auth\LoginController@redirectToProvider');
    Route::get('login/facebook/callback', 'Auth\LoginController@handleProviderCallback');

    /** BOOKS */
    /** show books list */
    Route::get('/book', 'BookController@index');
    /** show blocks of book */
    Route::get('/book/{book}', 'BookController@show');
    /** save new book */
    Route::post('/book', 'BookController@store');
    /** update book */
    Route::put('/book/{book}', 'BookController@update');
    /** delete book */
    Route::delete('/book/{book}', 'BookController@destroy');

    /** show list books of folder */
    Route::get('/get-books/{folder}', 'BookController@getBooks');

    /** FOLDERS */
    /** show folders list */
    Route::get('/folder/', 'FolderController@index');
    /** show blocks of folder */
    Route::get('/folder/{folder}', 'FolderController@show');
    /** save new folder */
    Route::post('/folder', 'FolderController@store');
    /** update folder */
    Route::put('/folder/{folder}', 'FolderController@update');
    /** delete folder */
    Route::delete('/folder/{folder}', 'FolderController@destroy');

    /** UPLOAD */
//        Route::get('/upload',['as' => 'upload_form', 'uses' => 'UploadController@getForm']);
    Route::post('/upload',['as' => 'upload_file','uses' => 'UploadController@upload']);

    /** TRANSLATE */
    Route::post('/get-book',['as' => 'get_book', 'uses' => 'TranslateController@getBook']);
    Route::post('/translate',['as' => 'translate', 'uses' => 'TranslateController@translate']);
    Route::post('/load-page',['as' => 'load_page', 'uses' => 'TranslateController@loadPage']);
});
//});