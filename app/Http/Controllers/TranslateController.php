<?php

namespace App\Http\Controllers;

use App\Book;
use App\Page;
use App\Word;
use Illuminate\Http\Request;

class TranslateController extends Controller
{

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
        return response()->json($book_array, 200);
    }


    /**
     * @param Request $request book_id, current_page
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadPage(Request $request){

        $all_pages = Page::where([
            'book_id' => $request->book_id,
        ])
            ->get();

        foreach ($all_pages as $item) {
            $keys_pages[] = $item->id;
        }

        $pages['prev_page'] = $this->prevPage($keys_pages, $request->book_id, $request->current_page);

        $pages['current_page'] = Page::where([
                'book_id' => $request->book_id,
                'id' => $request->current_page
            ])
            ->get();

        $pages['next_page'] = $this->nextPage($keys_pages, $request->book_id, $request->current_page);

        return response()->json($pages, 200);
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
