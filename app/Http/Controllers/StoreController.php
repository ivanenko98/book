<?php

namespace App\Http\Controllers;

use App\Book;
use App\Genre;
use App\Page;
use App\PurchasedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class StoreController extends Controller
{
    public $number = 10;

    public function getPopularBooks()
    {
        $books = Book::where('store', 1)->orderBy('buyers', 'desc')->limit(10)->get();

        return $this->formatResponse('success', null, $books);
    }

    /**
     * @return array
     */
    public function getRecommendedBooks()
    {
        $user = Auth::user();

        $purchased_books = PurchasedBook::where('buyer_id', $user->id)->get();

        $books = Book::where('store', 1)->get();

        if ($books->count() <= $this->number) {
            return $this->formatResponse('success', null, $books);
        }

        $recommended_books = [];

        if ($purchased_books->count() > 0) {


            while (collect($recommended_books)->count() < $this->number) {

                $genres = [];

                foreach ($purchased_books as $purchased_book) {

                    if (!in_array($purchased_book->book->genre_id, $genres)) {
                        $genres[] = $purchased_book->book->genre_id;
                    }
                }

                foreach ($genres as $genre_id) {
                    $books = Book::where(
                        ['genre_id', $genre_id],
                        ['store', 1]
                    )->orderBy('buyers', 'desc')->get();

                    if ($books->count() > 0) {

                        $recommended_book = $books->first(function ($book, $key) use ($recommended_books) {
                            if (collect($recommended_books)->contains('id', $book->id) == false) {
                                return true;
                            } else {
                                return false;
                            }
                        });

                        if ($recommended_book !== null) {
                            $recommended_books[] = $recommended_book;
                        }


                        if (collect($recommended_books)->count() >= $this->number) {
                            return $this->formatResponse('success', null, collect($recommended_books));
                        }
                    }
                }
            }
        } else {
            $books = Book::where('store', 1)->orderBy('buyers', 'desc')->limit(10)->get();
            return $this->formatResponse('success', null, $books);
        }

        return $this->formatResponse('success', null, collect($recommended_books));
    }

    public function getNewBooks()
    {
        $books = Book::where('store', 1)->orderBy('created_at', 'desc')->limit(10)->get();

        return $this->formatResponse('success', null, $books);
    }

    public function getSoldBooks()
    {
        $user = Auth::user();

        $purchased_books = $user->soldBooks;

        return $this->formatResponse('success', null, $purchased_books);
    }

    public function getPurchasedBooks()
    {
        $user = Auth::user();

        $purchased_books = $user->purchasedBooks->where('status', 'available');

        return $this->formatResponse('success', null, $purchased_books);
    }

    public function buyBook(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'buyer_id'            => 'required',
            'book_id'             => 'required',
            'seller_id'           => 'required',
            'price'               => 'required',
        ]);

        if ($validator->fails()){
            $response = $this->arrayResponse('error','incorrect data', $validator->errors());
            return response($response, 200);
        }

        $purchased_book_db = PurchasedBook::where([
            ['buyer_id', $request->buyer_id],
            ['book_id', $request->book_id]
        ])->get()->first();

        if ($purchased_book_db !== null) {
            return $this->formatResponse('error', 'this book is already bought');
        }

        $book = Book::find($request->book_id);

        $book->save();

        $purchased_book = new PurchasedBook();

        $purchased_book->buyer_id = $request->buyer_id;
        $purchased_book->seller_id = $request->seller_id;
        $purchased_book->book_id = $request->book_id;
        $purchased_book->price = $request->price;

        $purchased_book->save();

        return $this->formatResponse('success', null);
    }

    public function archivingBook(Request $request)
    {
        $user = Auth::user();

        foreach ($request->books as $book_id) {
            $purchased_book = PurchasedBook::where([
                ['buyer_id', $user->id],
                ['book_id', $book_id],
            ])->get()->first();

            if ($purchased_book == null) {
                return $this->formatResponse('error', 'book not found id: '. $book_id);
            }

            $purchased_book->status = 'archived';

            $purchased_book->save();
        }

        return $this->formatResponse('success', null);
    }

    public function restoreBook(Request $request)
    {
        $user = Auth::user();

        foreach ($request->books as $book_id) {
            $purchased_book = PurchasedBook::where([
                ['buyer_id', $user->id],
                ['book_id', $book_id],
            ])->get()->first();

            if ($purchased_book == null) {
                return $this->formatResponse('error', 'book not found id: '. $book_id);
            }

            $purchased_book->status = 'available';

            $purchased_book->save();
        }

        return $this->formatResponse('success', null);
    }

    public function listArchivedBooks()
    {
        $user = Auth::user();

        $archived_books = PurchasedBook::where([
            ['buyer_id', $user->id],
            ['status', 'archived'],
        ])->get();

        return $this->formatResponse('success', null, $archived_books);
    }

    public function getListGenres()
    {
        $genres = Genre::all();
        return $this->formatResponse('success', null, $genres);
    }

    public function bookToStore(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'book_id'             => 'required',
            'genre_id'            => 'required'
        ]);

        if ($validator->fails()){
            $response = $this->arrayResponse('error','incorrect data', $validator->errors());
            return response($response, 200);
        }

        $book = Book::find($request->book_id);

        if ($book == null) {
            $response = $this->arrayResponse('error','book not found');
            return response($response, 200);
        }

        $book->store = 1;
        $book->genre_id = $request->genre_id;

        $book->save();

        $response = $this->arrayResponse('success',null);
        return response($response, 200);
    }

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

    private function keysPages($allPages){
        foreach ($allPages as $item) {
            $this->keys_pages[] = $item->id;
        }
        return $this->keys_pages;
    }
}
