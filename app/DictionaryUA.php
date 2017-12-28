<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryUA extends Model
{
    protected $table = 'dct-ua';

    public function translate_en()
    {
        return $this->belongsToMany('App\DictionaryEN', 'dct-en_dct-ua', 'ua-id', 'en-id');
    }
}
