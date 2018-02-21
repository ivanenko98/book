<?php

namespace App\Http\Controllers;

use App\Book;
use App\Folder;
use App\Genre;
use App\Page;
use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $ifBook;

    public $data;

    public function index()
    {
        $user = Auth::user();
        $books = Book::where('user_id', $user->id)->get();
        foreach ($books as $book) {
            $this->data[] = [
                'id' => $book->id,
                'name' => $book->name,
                'description' => $book->description,
                'author' => $book->author,
                'likes' => $book->likes,
                'folder_id' => $book->folder_id,
                'user_id' => $book->user_id,
                'genre_id' => $book->genre_id,
                'created_at' => $book->created_at,
                'reviews' => Review::where('book_id', $book->id)->get(),
            ];
        }
        return response()->json($this->data, 200);
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
        return response()->json($book, 200);
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
        return response()->json($book, 200);
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
        return response()->json($book, 200);
    }

    public function updateFolder(Request $request, Book $book, Folder $folder){
       $booksId = $request->book_id;
        $newFolderId = $request->folder_id;
        $folderObject = $folder->where('id', $newFolderId)->select('name')->get();
        $folderName = $folderObject['0']->name;

        foreach ($booksId as $bookId){
            $ifBook = $book->where('id', $bookId)->get()->first();
            if (!$ifBook == null){
                $book->where('id', $bookId)->update(['folder_id' => $newFolderId]);
                return response()->json([
                    "Book was added to " . $folderName . " folder successfully"
                ], 200);
            } else {
                return response()->json([
                    "Book doesnt exist"
                ]);
            }
        }

       return response()->json([
            "Folder was updated successfully"
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Book $book)
    {
        $ids = $request->all();
        foreach ($ids as $id) {

            $check = $book->where('user_id', Auth::user()->id);

            if ($check == true){
                $book->pages->where('book_id', $id)->delete();

            } else {
                dd('error');
            }

            $book->where('id', $id)->delete();
        }
        return response()->json(['success'=>"Products Deleted successfully."]);

    }

    public function searchBooks(Request $request)
    {
        $keyword = $request->keyword;

        $books = Book::where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%");
        })->orderBy('id', 'desc')->get();

        $response = $this->arrayResponse('success', null, $books);
        return response($response, 200);
    }
}
