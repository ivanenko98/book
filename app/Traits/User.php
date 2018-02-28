<?php
/**
 * book.
 * User: Serg
 * Date: 28.02.2018
 * Time: 15:09
 */

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait User
{

    public function ifUser($book){
        $user = Auth::user();
        if ($book->where('user_id', $user->id)){
            return true;
        }
        return false;
    }

}