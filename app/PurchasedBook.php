<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchasedBook extends Model
{
    public function buyer(){
        return $this->belongsTo(User::class, 'id', 'buyer_id');
    }

    public function seller(){
        return $this->belongsTo(User::class, 'id', 'seller_id');
    }

    public function book(){
        return $this->belongsTo(Book::class);
    }
}
