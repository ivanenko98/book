<?php

namespace App\Http\Controllers;

use App\Book;
use App\Genre;
use App\PurchasedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StoreController extends Controller
{
    public $number = 10;

    public function getPopularBooks()
    {
        $books = Book::orderBy('buyers', 'desc')->limit(10)->get();

        return $this->formatResponse('success', null, $books);
    }

    /**
     * @return array
     */
    public function getRecommendedBooks()
    {
        $user = Auth::user();

        $purchased_books = PurchasedBook::where('buyer_id', $user->id)->get();

        $books = Book::all();

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
                    $books = Book::where('genre_id', $genre_id)->orderBy('buyers', 'desc')->get();

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
            $books = Book::orderBy('buyers', 'desc')->limit(10)->get();
            return $this->formatResponse('success', null, $books);
        }

        return $this->formatResponse('success', null, collect($recommended_books));
    }

    public function getNewBooks()
    {
        $books = Book::orderBy('created_at', 'desc')->limit(10)->get();

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
        $purchased_book_db = PurchasedBook::where([
            ['buyer_id', $request->buyer_id],
            ['book_id', $request->book_id]
        ])->get()->first();

        if ($purchased_book_db !== null) {
            return $this->formatResponse('error', 'this book is already bought');
        }

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

        $purchased_book = PurchasedBook::where([
            ['buyer_id', $user->id],
            ['book_id', $request->book_id],
        ])->get()->first();

        if ($purchased_book == null) {
            return $this->formatResponse('success', 'book not found');
        }

        $purchased_book->status = 'archived';

        $purchased_book->save();

        return $this->formatResponse('success', null);
    }

    public function restoreBook(Request $request)
    {
        $user = Auth::user();

        $purchased_book = PurchasedBook::where([
            ['buyer_id', $user->id],
            ['book_id', $request->book_id],
        ])->get()->first();

        if ($purchased_book == null) {
            return $this->formatResponse('success', 'book not found');
        }

        $purchased_book->status = 'available';

        $purchased_book->save();

        return $this->formatResponse('success', null);
    }

    public function getListGenres()
    {
        $genres = Genre::all();

        return $this->formatResponse('success', null, $genres);
    }
}