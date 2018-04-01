<?php
/**
 * Created by PhpStorm.
 * User: westham
 * Date: 01.04.18
 * Time: 10:09
 */

namespace App\Http\Traits;


use App\Page;
use App\Word;
use Illuminate\Http\Request;

trait Translate
{
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