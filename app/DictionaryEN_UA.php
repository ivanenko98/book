<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryEN_UA extends Model
{
    protected $table = 'dcten_dctua';



    public function User()
    {
        return $this->belongsTo('App\User');
    }
}
