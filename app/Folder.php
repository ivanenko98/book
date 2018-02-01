<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{

    protected $fillable = ['name', 'user_id'];

    public function books(){
        return $this->hasMany(Book::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
