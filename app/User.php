<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use Notifiable;

    public $rating = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'surname', 'email', 'phone', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = [
        'rating'
    ];

    public function sendLinkToReset($token, $user)
    {
        $letter['from'] = 'book@gmail.com';
        $letter['subject'] = 'Reset the password';

        Mail::send('auth.passwords.forgot', compact('token'), function ($message) use ($letter, $user, $token){
            $message->from($letter['from'])
                ->to($user->email)
                ->subject($letter['subject']);
        });
    }
    public function generateToken()
    {
        $this->api_token = str_random(60);
        $this->save();

        return $this->api_token;
    }

    public function books(){
        return $this->hasMany(Book::class);
    }

    public function folders(){
        return $this->hasMany(Folder::class);
    }

    public function dictionaryEn()
    {
        return $this->hasMany('App\DictionaryEN');
    }

    public function dictionaryUa()
    {
        return $this->hasMany('App\DictionaryUA');
    }

    public function dictionaryEnUa()
    {
        return $this->hasMany('App\DictionaryEN_UA');
    }

    public function purchasedBooks()
    {
        return $this->hasMany('App\PurchasedBook', 'buyer_id');
    }

    public function soldBooks()
    {
        return $this->hasMany('App\PurchasedBook', 'seller_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getRatingAttribute()
    {
        $count_reviews = 0;
        foreach ($this->soldBooks as $soldBook) {

            foreach ($soldBook->book->reviews as $review) {
                $this->rating = (int)$this->rating + (int)$review->rating;
                $count_reviews = $count_reviews + $soldBook->book->reviews->count();
            }
        }

        if ($this->rating == null) {
            return null;
        } else {
            $sum = $this->rating/$count_reviews;
            return $sum;
        }
    }
}
