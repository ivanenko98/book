<?php

namespace App\Http\Controllers;

use App\Book;
use App\DictionaryUA;
use App\Page;
use App\Word;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\TranslateClient;

class TranslateController extends Controller
{

    public $words;

    public $keys_pages;

    public $relationAllWords;

    public $pages;

    public $book_array;
    /**
     * return one book(full)
     *
     * @param Request $request book_id
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
//        return $book_array;
    }


    /**
     * @param Request $request book_id, current_page
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadPage(Request $request){

        $allPages = $this->allPages($request);

        $keysPages = $this->keysPages($allPages);

        $prev_page = $this->prevPage($keysPages, $request->book_id, $request->current_page);

        $next_page = $this->nextPage($keysPages, $request->book_id, $request->current_page);

        $current_page = Page::where([
            'book_id' => $request->book_id,
            'id' => $request->current_page
        ])
            ->get();


        if ($prev_page != null) {
            $pages['prev_page'] = $prev_page;
        }

        if ($current_page != null) {
            $pages['current_page'] = $current_page;
        }

        if ($next_page != null) {
            $pages['next_page'] = $next_page;
        }

        $bookArray = $this->bookArray($allPages);

        return $bookArray;
    }

    private function allPages(Request $request){

        $ifPages = Page::where([
            'book_id' => $request->book_id,
        ])
            ->exists();

        if ($ifPages == true){
            $all_pages = Page::where([
                'book_id' => $request->book_id,
            ])
                ->get();

            return $all_pages;

        } else {
            return response()->json([
                'Message' => 'This book does not exist.'
            ]);
        }

    }

    private function bookArray($pages){

        foreach ($pages as $page){

            preg_match_all("/.*?[.?!](?:\s|$)/s", $page->content, $items);
            foreach ($items[0] as $item){
                $this->book_array['book']['page'.'-'. $page->id][] = $this->wordToObject($item);
            }
        }
        return $this->book_array;
    }

    private function keysPages($allPages){
        foreach ($allPages as $item) {
            $this->keys_pages[] = $item->id;
        }
        return $this->keys_pages;
    }

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

    private function prevPage($keys_pages, $book_id, $current_page){

        if ($keys_pages[0] !== $current_page){
            if(isset($keys_pages)){
                foreach ($keys_pages as $key_page) {

                    if($key_page == $current_page){
                        $id_prev_page = $keys_pages[array_search($key_page, $keys_pages) - 1];

                        $prev_page = Page::where([
                            'book_id' => $book_id,
                            'id' => $id_prev_page
                        ])
                            ->get();

                        return $prev_page;
                    }
                }
            }
        }else{
            return null;
        }
    }

    private function nextPage($keys_pages, $book_id, $current_page){

        if (end($keys_pages)!== $current_page){
            if(isset($keys_pages)){
                foreach ($keys_pages as $key_page) {

                    if($key_page == $current_page){
                        $id_next_page = $keys_pages[array_search($key_page, $keys_pages) + 1];

                        $next_page = Page::where([
                            'book_id' => $book_id,
                            'id' => $id_next_page
                        ])
                            ->get();

                        return $next_page;
                    }
                }
            }
        }else{
            return null;
        }
    }
}
