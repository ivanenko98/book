<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryEN extends Model
{
    protected $table = 'dcten';

    public function translateUa()
    {
        return $this->belongsToMany('App\DictionaryUA', 'dcten_dctua', 'en_id', 'ua_id');
    }

    public function User()
    {
        return $this->belongsTo('App\User');
    }
}
