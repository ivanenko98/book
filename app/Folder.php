<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{

    protected $fillable = ['name', 'user_id'];

    public function books(){
        return $this->hasMany(Book::class);
    }

    protected static function boot() {

        parent::boot();
        static::deleting(function($folder) {
            foreach ($folder->books as $book){
                $book->pages()->delete();
                $book->reviews()->delete();
            }
            $folder->books()->delete();
        });
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
