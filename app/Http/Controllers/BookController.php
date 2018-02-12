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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $book = Book::create($request->all());
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Book $book)
    {
        $book->update($request->all());
        return response()->json($book, 200);
    }

    public function updateFolder(Request $request, Book $book, Folder $folder){
//        $da = $request->all();
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

//        $book->whereIn('id', $booksId)->update(['folder_id' => $newFolderId]);
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
//        $book->delete($request->all());
//        return response()->json('deleted', 200);

        $ids = $request->all();
        foreach ($ids as $id) {

            $check = $book->where('user_id', Auth::user()->id);

            if ($check == true){
                $book->pages->where('book_id', $id)->delete();

            } else {
                dd('error');
            }

            $book->where('id', $id)->delete();

//            $pages = $book->pages->where('book_id', $id);
//            foreach ($pages as $page) {
//                if($page != null) {
//                    $page->delete();
//                }
//            }

        }
        return response()->json(['success'=>"Products Deleted successfully."]);

    }

    public function genres(Genre $genre){
        $allGenres = Genre::all();
        dd($allGenres);
    }
}
