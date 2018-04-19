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
    const NUMBER = 10;

    public function getPopularBooks()
    {
        $books = Book::where('store', 1)->orderBy('buyers', 'desc')->limit(10)->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    /**
     * @return array
     */
    public function getRecommendedBooks()
    {
        $user = Auth::user();

        $purchased_books = PurchasedBook::where('buyer_id', $user->id)->get();

        $books = Book::where('store', 1)->get();

        if ($books->count() <= self::NUMBER) {
            $response = $this->formatResponse('success', null, $books);
            return response($response, 200);
        }

        $recommended_books = [];


        if ($purchased_books->count() > 0) {

            while (collect($recommended_books)->count() < self::NUMBER) {

                $genres = [];

                foreach ($purchased_books as $purchased_book) {
                    if (!in_array($purchased_book->book->genre_id, $genres)) {
                        $genres[] = $purchased_book->book->genre_id;
                    }
                }

                $books = Book::whereIn('genre_id', $genres)->get();

                if ($books->count() < self::NUMBER) {

                    $other_books = Book::whereNotIn('genre_id', $genres)
                        ->take(self::NUMBER - $books->count())
                        ->get();

                    $books = $books->merge($other_books);

                    $response = $this->formatResponse('success', null, $books);
                    return response($response, 200);
                }

                foreach ($genres as $genre_id) {
                    $books = Book::where(
                        ['genre_id' => $genre_id],
                        ['store' => 1]
                    )->orderBy('buyers', 'desc')->get();

                    if ($books->count() > 0) {

                        $recommended_book = $books->first(function ($book) use ($recommended_books) {
                            if (collect($recommended_books)->contains('id', $book->id) == false) {
                                return true;
                            } else {
                                return false;
                            }
                        });

                        if ($recommended_book !== null) {
                            $recommended_books[] = $recommended_book;
                        }

                        if (collect($recommended_books)->count() >= self::NUMBER) {
                            $response = $this->formatResponse('success', null, collect($recommended_books));
                            return response($response, 200);
                        }
                    }
                }
            }
        } else {
            $books = Book::where('store', 1)->orderBy('buyers', 'desc')->limit(10)->get();
            $response = $this->formatResponse('success', null, $books);
            return response($response, 200);
        }

        $response = $this->formatResponse('success', null, collect($recommended_books));
        return response($response, 200);
    }

    public function getNewBooks()
    {
        $books = Book::where('store', 1)->orderBy('created_at', 'desc')->limit(10)->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    public function getSoldBooks()
    {
        $user = Auth::user();

        $purchased_books = $user->soldBooks;

        $response = $this->formatResponse('success', null, $purchased_books);
        return response($response, 200);
    }

    public function getPurchasedBooks()
    {
        $user = Auth::user();

        $purchased_books = $user->purchasedBooks()->whereIn('status', ['available', 'demonstration'])->get();

        $response = $this->formatResponse('success', null, $purchased_books);
        return response($response, 200);
    }

    public function buyBook(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'book_id'             => 'required',
            'demonstration'       => 'required',
        ]);

        if ($validator->fails()){
            $response = $this->formatResponse('error','incorrect data', $validator->errors());
            return response($response, 200);
        }

        $purchased_book_db = PurchasedBook::where([
            'buyer_id' => Auth::user()->id,
            'book_id' => $request->book_id
        ])->get()->first();

        $book = Book::find($request->book_id);
        if ($purchased_book_db !== null) {
            if ($purchased_book_db->status == 'demonstration') {
                if ($request->demonstration == 1) {
                    $response = $this->formatResponse('error', 'demo version book already buyed');
                    return response($response, 200);
                } else {
                    $purchased_book_db->status = 'available';
                    $purchased_book_db->price = $book->price;
                    $purchased_book_db->save();

                    $response = $this->formatResponse('success');
                    return response($response, 200);
                }
            } else {
                $response = $this->formatResponse('error', 'this book is already bought');
                return response($response, 200);
            }
        }

        $book->buyers = $book->buyers + 1;

        $book->save();

        $purchased_book = new PurchasedBook();

        $purchased_book->buyer_id = Auth::user()->id;
        $purchased_book->seller_id = $book->user_id;
        $purchased_book->book_id = $request->book_id;
        $purchased_book->price = $book->price;

        if ($request->demonstration == 1) {
            $purchased_book->status = 'demonstration';
        }

        $purchased_book->save();

        $response = $this->formatResponse('success');
        return response($response, 200);
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
                $response = $this->formatResponse('error', 'book not found id: '. $book_id);
                return response($response, 200);
            }

            $purchased_book->status = 'archived';

            $purchased_book->save();
        }

        $response = $this->formatResponse('success');
        return response($response, 200);
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
                $response = $this->formatResponse('error', 'book not found id: '. $book_id);
                return response($response, 200);
            }

            $purchased_book->status = 'available';

            $purchased_book->save();
        }

        $response = $this->formatResponse('success');
        return response($response, 200);
    }

    public function listArchivedBooks()
    {
        $user = Auth::user();

        $archived_books = PurchasedBook::where([
            ['buyer_id', $user->id],
            ['status', 'archived'],
        ])->get();

        $response = $this->formatResponse('success', null, $archived_books);
        return response($response, 200);
    }

    public function getListGenres()
    {
        $genres = Genre::all();

        $response = $this->formatResponse('success', null, $genres);
        return response($response, 200);
    }

    public function bookToStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id'             => 'required',
            'price'               => 'required',
            'genre_id'            => 'required'
        ]);

        if ($validator->fails()){
            $response = $this->formatResponse('error','incorrect data', $validator->errors());
            return response($response, 200);
        }

        $book = Book::find($request->book_id);

        if ($book == null) {
            $response = $this->formatResponse('error','book not found');
            return response($response, 200);
        }

        $book->store = 1;
        $book->price = $request->price;
        $book->genre_id = $request->genre_id;

        $book->save();

        $response = $this->formatResponse('success',null);
        return response($response, 200);
    }
}