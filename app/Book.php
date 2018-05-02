<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    protected static $books = array();

    protected $fillable = ['name', 'description', 'author', 'likes', 'percent', 'folder_id', 'user_id'];

    protected $visible = [
        'id',
        'name',
        'description',
        'author',
        'likes',
        'percent',
        'folder_id',
        'user_id',
        'image',
        'created_at',
        'updated_at',
        'genre_name',
        'pages_count',
        'status',
        'rating',
        'genre',
        'translator',
    ];

    protected $appends = [
        'pages_count',
        'rating',
        'genre',
        'translator',
        'status',
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
        return $this->hasMany(PurchasedBook::class);
    }

    public function genre(){
        return $this->belongsTo(Genre::class);
    }

    public function reviews(){
        return $this->hasMany(Review::class);
    }


    /** ATTRIBUTE */

    public function getPagesCountAttribute()
    {
        $user = Auth::user();

        $purchased_book = $this->purchases()->where('buyer_id', $user->id)->first();

        if ($purchased_book != null) {
            if ($purchased_book->status == 'demonstration') {
                return Config::get('constants.demonstration_pages');
            }
        }

        $pages = Page::where('book_id', $this->id)->get();

        if ($pages !== null) {
            return $pages->count();
        } else {
            return null;
        }
    }

    public function getStatusAttribute()
    {
        $user = Auth::user();

        $purchased_book = $this->purchases()->where('buyer_id', $user->id)->first();

        if ($purchased_book != null) {
            return $purchased_book->status;
        }

        return null;
    }

    public function getRatingAttribute()
    {
        $reviews = Review::where('book_id', $this->id)->get();

        $rating = 0;
        $count_reviews = 0;
        foreach ($reviews as $review) {
            $rating = (int)$rating + (int)$review->rating;
            $count_reviews = $count_reviews + 1;
        }

        if ($rating == null) {
            return null;
        } else {
            $sum = $rating/$count_reviews;
            return $sum;
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
            return $translator->name;
        } else {
            return null;
        }
    }

    public static function purchasedBooks($user, $field = 'status', $values = ['available'])
    {
        $purchased_books = $user->purchasedBooks()->whereIn($field, $values)->get();

        foreach ($purchased_books as $purchased_book) {
            self::$books[] = $purchased_book->book;
        }

        return self::$books;
    }

    protected static function boot() {

        parent::boot();
        static::deleting(function($book) {
            $book->pages()->delete();
            $book->reviews()->delete();
            $book->purchases()->delete();
        });
    }
}
