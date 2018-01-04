<?php

namespace App\Http\Controllers;

use App\Book;
use App\DictionaryUA;
use App\Page;
use App\Word;
use Illuminate\Http\Request;

class TranslateController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBook(Request $request){

        $book = Book::find($request->book_id);

        foreach ($book->pages as $page){
            preg_match_all("/.*?[.?!](?:\s|$)/s", $page->content, $items);
            foreach ($items[0] as $item){
                $book_array['book']['page'.'-'. $page->id][] = $this->wordToObject($item);
            }
        }
//        return response()->json($book_array, 200);
        return $book_array;
    }

//    public function



    /**
     * @param $sentence
     * @return array
     */
    private function wordToObject($sentence){
        $separator = " \t\n";

        $array_words = array();
        $tok = strtok($sentence, $separator);

        while($tok) {
            $first_symbol = utf8_encode(substr($tok,1,1));
            $last_symbol = utf8_encode(substr($tok, -1));

            if ($first_symbol == '«'){
                $tok = substr($tok, 2);
                if ($last_symbol == '»') {
                    $tok = substr($tok, 0, -2);
                    $word = new Word($tok, $first_symbol, $last_symbol);
                } else if ($last_symbol == ',' || $last_symbol == '.' || $last_symbol == '?' || $last_symbol == '!' || $last_symbol == ':' || $last_symbol == ';' || $last_symbol == '-'){
                    $tok = substr($tok, 0, -1);
                    $word = new Word($tok, $first_symbol, $last_symbol);
                } else {
                    $word = new Word($tok, $first_symbol);
                }
            } else {
                if ($last_symbol == ',' || $last_symbol == '.' || $last_symbol == '?' || $last_symbol == '!' || $last_symbol == ':' || $last_symbol == ';' || $last_symbol == '-'){
                    $tok = substr($tok, 0, -1);
                    $word = new Word($tok, null,$last_symbol);
                } else if ($last_symbol == '»') {
                    $tok = substr($tok, 0, -2);
                    $word = new Word($tok, null,$last_symbol);
                } else {
                    $word = new Word($tok);
                }
            }

            $array_words[] = $word;
            $tok = strtok(" \t\n");

        }
        return $array_words;

    }

    public function translate(Request $request){
        $allTranslateWords = DictionaryUA::all();


        $book = $this->getBook($request);

        foreach ($book['book'] as $bookPages) {
            foreach ($bookPages as $bookWords) {
                foreach ($bookWords as $bookWord){
                    $words[$bookWord->id] = $bookWord->name;
                }
            }
        }

        foreach ($allTranslateWords as $allTranslateWord){
            $allwords[] = $allTranslateWord->translateEn;
            foreach ($allwords as $allword){
                foreach ($allword as $all){
                    $relationAllWords[$all->word] = $all->pivot->pivotParent->word;
                }
            }
        }

        $sameWords = array_intersect($relationAllWords, $words);

        return $sameWords;


    }


}
