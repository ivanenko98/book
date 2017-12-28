<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryEN extends Model
{
    protected $table = 'dct-en';

    public function translate_ua()
    {
        return $this->belongsToMany('App\DictionaryUA', 'dct-en_dct-ua', 'en-id', 'ua-id');
    }
}
