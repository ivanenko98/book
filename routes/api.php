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

Route::post('/register', 'Auth\RegisterController@register');
Route::post('/login', 'Auth\LoginController@login');

/** FOLDERS */
/** show folders list */
Route::get('/folder', 'FolderController@index');
/** show blocks of folder */
Route::get('/folder/{folder}', 'FolderController@show');
/** save new folder */
Route::post('/folder', 'FolderController@store');
/** update folder */
Route::put('/folder/{folder}', 'FolderController@update');
/** delete folder */
Route::delete('/folder/{folder}', 'FolderController@destroy');


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
Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::post('user/reset-password', 'Auth\ResetPasswordController@userPasswordReset');
