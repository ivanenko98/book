<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['content', 'book_id'];

    protected $appends = ['order_number'];

    protected $hidden = ['book'];

    public function book(){
        return $this->belongsTo(Book::class);
    }

    public function getOrderNumberAttribute()
    {
        $book = $this->book;

        $pages = Page::where('book_id', $book->id)->orderBy('id', 'asc')->get();

        foreach ($pages as $key => $page) {
            if ($page->id == $this->id) {
                return $key + 1;
            }
        }
        return null;
    }
}
