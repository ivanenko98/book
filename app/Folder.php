<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{

    protected $fillable = ['name'];

    public function books(){
        return $this->hasMany(Book::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
