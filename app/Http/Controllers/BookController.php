<?php

namespace App\Http\Controllers;

use App\Book;
use App\Folder;
use App\Genre;
use App\Http\Requests\ImageRequest;
use App\Http\Traits\Translate;
use App\Page;
use App\Review;
use App\Traits\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    use Translate;

    const DEMONSTRATION_PAGES = 10;

    public $ifBook;

    public $data;

    public function index()
    {
        $user = Auth::user();
        $books = Book::where('user_id', $user->id)->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    public function getBooks(Folder $folder)
    {
        $books = Book::where('folder_id', $folder->id)->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $book = Book::create($request->except('percent'));

        $response = $this->formatResponse('success', null, $book);
        return response($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Book $book
     *
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show(Book $book)
    {
        $book = Book::find($book->id);

        $response = $this->formatResponse('success', null, $book);
        return response($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        $book->update($request->all());

        $response = $this->formatResponse('success', null, $book);
        return response($response, 200);
    }

    public function updateFolder(Request $request){
       $booksId = $request->book_id;
        $newFolderId = $request->folder_id;
        $folderObject = Folder::where('id', $newFolderId)->select('name')->get();
        $folderName = $folderObject['0']->name;

        foreach ($booksId as $bookId){
            $ifBook = Book::where('id', $bookId)->get()->first();
            if (!$ifBook == null){
                Book::where('id', $bookId)->update(['folder_id' => $newFolderId]);

                $response = $this->formatResponse('success', 'Book was added to ' . $folderName . 'folder successfully');
                return response($response, 200);
            } else {
                $response = $this->formatResponse('success', 'Book does not exist');
                return response($response, 200);
            }
        }

        $response = $this->formatResponse('success', 'Folder was updated successfully');
        return response($response, 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->all();

        foreach ($ids as $id) {

            $book = Book::find($id);

            if ($this->ifUser($book) == true){
                $book->delete();
            } else {
                $response = $this->formatResponse('error');
                return response($response, 200);
            }
        }

        $response = $this->formatResponse('success', 'Products Deleted successfully');
        return response($response, 200);
    }

    public function searchBooks(Request $request)
    {
        $keyword = $request->keyword;

        $books = Book::where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
        })->orderBy('id', 'desc')->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    /** Need for mobile app
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function listBooks(Request $request)
    {
        $books = Book::where('genre_id', $request->genre_id)->get();

        $response = $this->formatResponse('success', null, $books);
        return response($response, 200);
    }

    public function uploadImage(ImageRequest $request)
    {
        $book = Book::find($request->book_id);

        if ($book == null) {
            $response = $this->formatResponse('error', 'book not found');
            return response($response, 200);
        }

        if ($book->image !== null) {
            Storage::disk('custom')->delete($book->image);
        }

        $imageName = time().'.'.request()->image->getClientOriginalExtension();

        Storage::disk('custom')->putFileAs('', $request->image, $imageName);

        $book->image = $imageName;
        $book->save();

        $response = $this->formatResponse('success', null);
        return response($response, 200);
    }

    public function getFullText(Request $request)
    {
        $allPages = $this->allPages($request);

        $bookArray = $this->bookArray($allPages);

        return $bookArray;
    }

    public function listPages(Request $request)
    {
        $user = Auth::user();

        $purchased_book = $user->purchasedBooks->where('book_id', $request->book_id)->first();

        if ($purchased_book !== null && $purchased_book->status == 'demonstration') {
            $allPages = $purchased_book->book->pages->take(Config::get('constants.demonstration_pages'));
        } else {
            $allPages = $this->allPages($request);
        }

        $response = $this->formatResponse('success', null, $allPages);
        return response($response, 200);
    }

    public function loadPage(Request $request)
    {
        $user = Auth::user();

        $purchased_book = $user->purchasedBooks->where('book_id', $request->book_id)->first();

        if ($purchased_book !== null && $purchased_book->status == 'demonstration') {
            $allPages = $purchased_book->book->pages->take(Config::get('constants.demonstration_pages'));
        } else {
            $allPages = $this->allPages($request);
        }

        $keysPages = $this->keysPages($allPages);

        $current_page = Page::where('book_id', $request->book_id)
            ->skip($request->current_page - 1)
            ->limit(1)
            ->first();

        $prev_page = $this->prevPage($keysPages, $request->book_id, $current_page->id);

        $next_page = $this->nextPage($keysPages, $request->book_id, $current_page->id);

        $pages = [];

        if ($prev_page != null) {
            $pages['prev_page'] = $prev_page;
        }

        if ($current_page != null) {
            $pages['current_page'] = $current_page;
        }

        if ($next_page != null) {
            $pages['next_page'] = $next_page;
        }

        $response = $this->formatResponse('success', null, $pages);
        return response($response, 200);
    }
}
