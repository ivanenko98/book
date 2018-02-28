<?php

namespace App\Http\Controllers;

use App\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public $genresBooks;

    public function index(){
//        $user = Auth::user();
        $genres = Genre::all();
        return response()->json($genres, 200);
    }
}
