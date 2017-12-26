<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['content', 'book_id'];

    public function book(){
        return $this->belongsTo(Book::class);
    }
}
