<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

}
