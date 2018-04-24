<?php

namespace App\Http\Controllers;

use App\Book;
use App\ChangePhoneRequest;
use App\Http\Requests\ImageRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Nexmo\Laravel\Facade\Nexmo;

class UserController extends Controller
{
    public function uploadImage(ImageRequest $request)
    {
        $user = Auth::user();

        if ($user == null) {
            $response = $this->formatResponse('error', 'user not found');
            return response($response, 200);
        }

        if ($user->image !== null) {
            Storage::disk('local')->delete('images/' . $user->image);
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();

        Storage::disk('local')->putFileAs('images', $request->image, $imageName);

        $user->image = $imageName;
        $user->save();

        $response = $this->formatResponse('success', null);
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
            $response = $this->formatResponse('error','incorrect data', $validator->errors());
            return response($response, 200);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            $response = $this->formatResponse('error','incorrect old password');
            return response($response, 200);
        }

        $user->password = bcrypt($request->password);

        $user->save();

        $response = $this->formatResponse('success', null);
        return response($response, 200);
    }

    public function getBooksInStore()
    {
        $user = Auth::user();

        $books = Book::where([
            ['user_id', $user->id],
            ['store', 1]
        ])->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    public function changeName(Request $request)
    {
        $user = Auth::user();

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('surname')) {
            $user->surname = $request->surname;
        }

        $user->save();

        $response = $this->formatResponse('success', null, $user);
        return response($response, 200);
    }

    public function sendSMSForChangePhone(Request $request)
    {
        $user = Auth::user();

        $phone_db = User::where('phone', $request->phone)->first();

        if ($phone_db !== null) {
            $response = $this->formatResponse('error','This number is already in use');
            return response($response, 200);
        }

        $old_request = ChangePhoneRequest::where('user_id', $user->id)->first();

        if ($old_request !== null) {
            $old_request->delete();
        }

        $code = rand(100000, 999999);

        $old_code = ChangePhoneRequest::where('code', $code)->first();

        while ($old_code !== null) {
            $code = rand(100000, 999999);
            $old_code = ChangePhoneRequest::where('code', $code)->first();
        }

        $new_request = new ChangePhoneRequest();
        $new_request->code = $code;
        $new_request->phone = $request->phone;
        $new_request->user_id = $user->id;
        $new_request->save();

        Nexmo::message()->send([
            'to'   => $request->phone,
            'from' => 'Book Translate',
            'text' => 'Your verification code: ' . $code
        ]);

        $response = $this->formatResponse('success',null);
        return response($response, 200);
    }

    public function changePhone(Request $request)
    {
        $user = Auth::user();

        $request_sms = ChangePhoneRequest::where('code', $request->code)->first();

        if ($request_sms == null) {
            $response = $this->formatResponse('error','code invalid');
            return response($response, 200);
        }

        if ($user->id !== $request_sms->user_id) {
            $response = $this->formatResponse('error','This request is not yours');
            return response($response, 200);
        }

        $user->update([
            'phone' => $request_sms->phone
        ]);

        $request_sms->delete();

        $response = $this->formatResponse('success',null);
        return response($response, 200);
    }
}
