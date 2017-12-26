<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

    protected $fillable = ['name', 'content', 'author', 'likes', 'percent', 'folder_id'];

    public function folder(){
        return $this->belongsTo(Folder::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function pages(){
        return $this->hasMany(Page::class);
    }
}
