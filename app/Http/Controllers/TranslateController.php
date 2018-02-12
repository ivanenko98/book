<?php

namespace App\Http\Controllers;

use App\Book;
use App\DictionaryUA;
use App\Page;
use App\Word;
use Illuminate\Http\Request;

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


        $bookArray = $this->bookArray($this->pages);


        return response()->json($bookArray, 200);

    }

    private function allPages(Request $request){
        $all_pages = Page::where([
            'book_id' => $request->book_id,
        ])
            ->get();
        return $all_pages;
    }


    private function bookArray($pages){
        foreach ($pages as $page){
            foreach ($page as $items){
                preg_match_all("/.*?[.?!](?:\s|$)/s", $items->content, $items1);
                foreach ($items1[0] as $item){
                    $this->book_array['book']['page'.'-'. $items->id][] = $this->wordToObject($item);
                }
            }
        }
        return $this->book_array;
    }

    private function keysPages($allPages){
        foreach ($allPages as $item) {
            dd($item);
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

    public function translate(Request $request){
        $allTranslateWords = DictionaryUA::all();


        $book = $this->loadPage($request);

//        dd($book);

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

//        dd($words);
//        dd($relationAllWords);

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

//        dd($book);

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
