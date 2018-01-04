<?php
/**
 * Created by PhpStorm.
 * User: westham
 * Date: 27.12.2017
 * Time: 13:13
 */

namespace App;


class Word
{
    public $name;
    public $first_symbol;
    public $last_symbol;
    public $is_translated;
    public $translated_word;
    public $id;

    public function __construct ($name, $first_symbol = null, $last_symbol = null, $is_translated = false, $translated_word = null)
    {
        $this->name = $name;
        $this->first_symbol = $first_symbol;
        $this->last_symbol = $last_symbol;
        $this->is_translated = $is_translated;
        $this->translated_word = $translated_word;
        $this->id = date("Ymdhis").rand(10000, 99999);
    }
}