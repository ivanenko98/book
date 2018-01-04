<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryUA extends Model
{
    protected $table = 'dctua';

    public function translateEn()
    {
        return $this->belongsToMany('App\DictionaryEN', 'dcten_dctua', 'ua_id', 'en_id');
    }

    public function User()
    {
        return $this->belongsTo('App\User');
    }
}
