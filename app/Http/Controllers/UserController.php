<?php

namespace App\Http\Controllers;

use App\Book;
use App\DictionaryEN_UA;
use App\Http\Requests\ImageRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function uploadImage(ImageRequest $request)
    {
        $user = Auth::user();

        if ($user == null) {
            $response = $this->arrayResponse('error', 'user not found');
            return response($response, 200);
        }

        if ($user->image !== null) {
            Storage::disk('local')->delete('images/' . $user->image);
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();

        Storage::disk('local')->putFileAs('images', $request->image, $imageName);

        $user->image = $imageName;
        $user->save();

        $response = $this->arrayResponse('success', null);
        return response($response, 200);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'password'             => 'required|confirmed',
            'old_password'         => 'required'
        ]);

        if ($validator->fails()){
            $response = $this->arrayResponse('error','incorrect data', $validator->errors());
            return response($response, 200);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            $response = $this->arrayResponse('error','incorrect old password');
            return response($response, 200);
        }

        $user->password = bcrypt($request->password);

        $user->save();

        $response = $this->arrayResponse('success', null);
        return response($response, 200);
    }

    public function getBooksInStore()
    {
        $user = Auth::user();

        $books = Book::where([
            ['user_id', $user->id],
            ['store', 1]
        ])->get();

        $response = $this->arrayResponse('success', null, $books);
        return response($response, 200);
    }

    public function addToDictionary(Request $request)
    {
        $word = DictionaryEN_UA::create(['']);
    }

}
