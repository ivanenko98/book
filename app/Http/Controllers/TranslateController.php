<?php

namespace App\Http\Controllers;

use App\Book;
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
        return response()->json($book_array, 200);
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
            $symbol = substr($tok, -1);

            if($symbol == ',' || $symbol == '.' || $symbol == '?' || $symbol == '!' || $symbol == ':' || $symbol == ';'){
                $tok = substr($tok, 0, -1);
                $word = new Word($tok, $symbol);
            }else{
                $word = new Word($tok);
            }

            $array_words[] = $word;
            $tok = strtok(" \t\n");
        }
        return $array_words;
    }
}
