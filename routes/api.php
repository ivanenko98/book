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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'Auth\RegisterController@register');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout');
Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
Route::post('user/reset-password', 'Auth\ResetPasswordController@userPasswordReset');

Route::group(['middleware' => 'auth:api'], function () {

    /** USER */
    /** get purchased books */
    Route::get('/purchased-books', 'StoreController@getPurchasedBooks');
    /** get sold books */
    Route::get('/sold-books', 'StoreController@getSoldBooks');
    /** archive book */
    Route::post('/archive-book', 'StoreController@archivingBook');
    /** restore book */
    Route::post('/restore-book', 'StoreController@restoreBook');


    /** FACEBOOK SOCIALITE */
    Route::get('login/facebook', 'Auth\LoginController@redirectToProvider');
    Route::get('login/facebook/callback', 'Auth\LoginController@handleProviderCallback');

    /** BOOKS */
    /** show books list */
    Route::get('/book', 'BookController@index');
    /** show blocks of book */
    Route::get('/book/{book}', 'BookController@show');
    /** show all genres*/
    Route::get('/genres', 'BookController@genres');
    /** save new book */
    Route::post('/book', 'BookController@store');
    /** update book */
    Route::put('/book/{book}', 'BookController@update');
    /** delete book */
    Route::post('/book-delete', 'BookController@destroy');
    /** change folder of book*/
    Route::post('/book-folder', 'BookController@updateFolder');
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
    Route::post('/upload',['as' => 'upload_file','uses' => 'UploadController@upload']);


    /** TRANSLATE */
    Route::post('/get-book',['as' => 'get_book', 'uses' => 'TranslateController@getBook']);
    Route::post('/translate',['as' => 'translate', 'uses' => 'TranslateController@translate']);
    Route::post('/load-page',['as' => 'load_page', 'uses' => 'TranslateController@loadPage']);


    /** REVIEWS */
    /** show reviews list */
    Route::get('/review/', 'ReviewController@index');
   /** save new folder */
    Route::post('/review', 'ReviewController@store');
    /** update folder */
    Route::put('/review/{review}', 'ReviewController@update');
    /** delete folder */
    Route::delete('/review/{review}', 'ReviewController@destroy');


    /** STORE */
    /** get popular books */
    Route::get('/get-popular-books', 'StoreController@getPopularBooks');
    /** get new books */
    Route::get('/get-new-books', 'StoreController@getNewBooks');
    /** get new books */
    Route::get('/get-recommended-books', 'StoreController@getRecommendedBooks');
    /** list genres */
    Route::get('/list-genres', 'StoreController@getListGenres');
    /** buy book */
    Route::post('/buy-book', 'StoreController@buyBook');
});