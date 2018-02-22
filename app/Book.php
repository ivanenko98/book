<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

    protected $fillable = ['name', 'description', 'author', 'likes', 'percent', 'folder_id', 'user_id'];

    protected $visible = [
        'name',
        'description',
        'author', 'likes',
        'percent',
        'folder_id',
        'user_id',
        'created_at',
        'updated_at',
        'genre_name',
        'pages',
        'reviews',
        'genre',
        'translator'
    ];

    protected $appends = [
        'pages',
        'reviews',
        'genre',
        'translator'
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

    public function purchases()
    {
        return $this->hasMany('App\PurchasedBook');
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
}
