<?php

namespace App\Http\Controllers;

use App\Book;
use App\Page;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    public function getBook(Request $request){

        $book = Book::find($request->book_id);

        foreach ($book->pages as $page){

            //Page to sentences
            preg_match_all("/.*?[.?!](?:\s|$)/s", $page->content, $items);

            foreach ($items[0] as $item){
                $book_array['book']['pages'][][] = $item;
            }

//



//            $book_array['book'][] = $page->content;

        }
        var_dump($book_array);
//        return $book_array;
    }
}
