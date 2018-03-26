<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DictionaryEN_UA extends Model
{
    protected $table = 'dcten_dctua';

    protected $appends = [
        'word',
        'translate'
    ];

    public function User()
    {
        return $this->belongsTo('App\User');
    }

    public function getWordAttribute()
    {
        $word = DictionaryEN::find($this->en_id);

        if ($word == null){
            return null;
        } else {
            return $word->word;
        }
    }

    public function getTranslateAttribute()
    {
        $word = DictionaryUA::find($this->ua_id);

        if ($word == null){
            return null;
        } else {
            return $word->word;
        }
    }
}
