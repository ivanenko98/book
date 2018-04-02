<?php

namespace App\Http\Controllers;

use App\DictionaryUA;
use App\Http\Traits\Translate;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslateController extends Controller
{
    use Translate;

    public $words;

    public $keys_pages;

    public $relationAllWords;

    public $pages;

    public $book_array;

    public function translateWord(Request $request){

        $wordName = $request->word_name;

        $translateFrom = 'uk';
        $translateTo = 'en';

        if(is_string($wordName)){
            $translateWord = $this->googleTranslate($wordName, $translateFrom, $translateTo);
            return $translateWord;
        } else {
            dd('This is not word!');
        }
    }

    public function googleTranslate($word, $translateFrom, $translateTo){
        $tr = new TranslateClient($translateFrom, $translateTo);
        return $tr->translate($word);
    }

    public function translate(Request $request){

        $allTranslateWords = DictionaryUA::all();


        $book = $this->loadPage($request);

        foreach ($book['book'] as $bookPages) {
            foreach ($bookPages as $bookWords) {
                foreach ($bookWords as $bookWord){

                    $bookWordName = $bookWord->name;

                    if (is_string($bookWordName)){

                        $bookWordNameLowerCase = mb_strtolower($bookWordName);

                        $this->words[$bookWord->id] = $bookWordNameLowerCase;
                    }
                }
            }
        }

        foreach ($allTranslateWords as $allTranslateWord){
            $allwords[] = $allTranslateWord->translateEn;
            foreach ($allwords as $allword){
                foreach ($allword as $all){
                    $this->relationAllWords[$all->word] = $all->pivot->pivotParent->word;
                }
            }
        }

        $sameWords = array_intersect($this->relationAllWords, $this->words);

        foreach ($book['book'] as $bookPages) {
            foreach ($bookPages as $bookWords) {
                foreach ($bookWords as $bookWord){
                    if (in_array($bookWord->name, $sameWords, true) ){
                        $translatedWord = array_search($bookWord->name, $sameWords);
                        $bookWord->is_translated = true;
                        $bookWord->translated_word = $translatedWord;
                    }
                }
            }
        }

        return $book;
    }
}
