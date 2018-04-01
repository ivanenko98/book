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
Route::post('/reset-password-send-sms', 'Auth\ResetPasswordController@resetPasswordSendSMS');
Route::post('/reset-password-from-sms', 'Auth\ResetPasswordController@resetPasswordFromSMS');

Route::group(['middleware' => 'auth:api'], function () {

    /** USER */
    /** change password */
    Route::post('/change-password', 'UserController@changePassword');
    /** change name */
    Route::post('/change-name', 'UserController@changeName');
    /** send code for change phone */
    Route::post('/send-change-phone', 'UserController@sendSMSForChangePhone');
    /** change phone */
    Route::post('/change-phone', 'UserController@changePhone');
    /** upload image user */
    Route::post('/image-user', 'UserController@uploadImage');
    /** get purchased books */
    Route::get('/purchased-books', 'StoreController@getPurchasedBooks');
    /** get sold books */
    Route::get('/sold-books', 'StoreController@getSoldBooks');
    /** archive book */
    Route::post('/archive-book', 'StoreController@archivingBook');
    /** restore book */
    Route::post('/restore-book', 'StoreController@restoreBook');
    /** list archived books */
    Route::get('/list-archived-books', 'StoreController@listArchivedBooks');
    /** show list books of store for translator */
    Route::get('/get-books-in-store', 'UserController@getBooksInStore');

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
    /** search books */
    Route::post('/search-books', 'BookController@searchBooks');
    /** list books of genre */
    Route::post('/list-books', 'BookController@listBooks');
    /** upload image book*/
    Route::post('/image-book', 'BookController@uploadImage');


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
    /** translating word using google translator */
    Route::post('/translate-word',['as' => 'translate', 'uses' => 'TranslateController@translateWord']);
    /** show all words of book as objects */
    Route::post('/get-full-text',['as' => 'full-text', 'uses' => 'BookController@getFullText']);
    /** show three pages (current_page, prev_page, next_page) */
    Route::post('/load-page',['as' => 'load_page', 'uses' => 'BookController@loadPage']);
    /** show list pages for book */
    Route::post('/list-pages', 'BookController@listPages');

    /** REVIEWS */
    /** show reviews list for book */
    Route::post('/list-reviews/', 'ReviewController@index');
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
    /** book to store */
    Route::post('/book-to-store', 'StoreController@bookToStore');


    /** DICTIONARY */
    /** list words */
    Route::get('/list-words', 'DictionaryController@listWords');
    /** add word to dictionary */
    Route::post('/add-word', 'DictionaryController@addToDictionary');
    /** remove word from dictionary */
    Route::post('/remove-word', 'DictionaryController@removeFromDictionary');
    /** search words */
    Route::post('/search-words', 'DictionaryController@searchWords');
});