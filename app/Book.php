<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{

    protected $fillable = ['name', 'description', 'author', 'likes', 'percent', 'folder_id', 'user_id'];

    protected $visible = [
        'id',
        'name',
        'description',
        'author', 'likes',
        'percent',
        'folder_id',
        'user_id',
        'image',
        'created_at',
        'updated_at',
        'genre_name',
        'pages',
        'reviews',
        'genre',
        'translator',
        'image_path'
    ];

    protected $appends = [
        'pages',
        'reviews',
        'genre',
        'translator',
        'image_path'
    ];

    public function folder(){
        return $this->belongsTo(Folder::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function pages(){
        return $this->hasMany(Page::class);
    }

    protected static function boot() {

        parent::boot();
        static::deleting(function($book) {
            $book->pages()->delete();
            $book->reviews()->delete();
        });
    }


    public function purchases()
    {
        return $this->hasMany(PurchasedBook::class);
    }

    public function genre(){
        return $this->belongsTo(Genre::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }


    /** ATTRIBUTE */

    public function getPagesAttribute()
    {
        $pages = Page::where('book_id', $this->genre_id)->get();

        if ($pages !== null) {
            return $pages->count();
        } else {
            return null;
        }
    }

    public function getReviewsAttribute()
    {
        $reviews = Review::where('book_id', $this->id)->get();

        if ($reviews !== null) {
            return $reviews;
        } else {
            return null;
        }
    }

    public function getGenreAttribute()
    {
        $genre = Genre::find($this->genre_id);

        if ($genre !== null) {
            return $genre->genre;
        } else {
            return null;
        }
    }

    public function getTranslatorAttribute()
    {
        $translator = User::find($this->user_id);

        if ($translator !== null) {
            return $translator;
        } else {
            return null;
        }
    }

    public function getImagePathAttribute()
    {
        $link = Storage::url($this->image);

        if ($link !== null) {
            return $link;
        } else {
            return null;
        }
    }
}
