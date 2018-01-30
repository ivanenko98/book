<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\UploadRequest;
use App\Page;
use Convertio\Convertio;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Auth;


class UploadController extends Controller
{
    public $filename;

    public function getForm()
    {
        return view('upload.upload-form');
    }

    public function upload(Request $request)
    {
        foreach ($request->file() as $files) {
            foreach ($files as $file) {
                $file->move(storage_path('docs'), time() . '_' . $file->getClientOriginalName());
                $this->filename = time() . '_' . $file->getClientOriginalName();
            }
        }

        $book = $this->createBook($request);

        if ($this->getExtension($this->filename) == 'pdf' || $this->getExtension($this->filename) == 'docx') {
            $text = $this->converter();
        }
        if (isset($text)){
            $this->cutToPages($text, $book);
        }

        return response()->json($book, 200);
    }

    function getExtension ($filename)
    {
        return explode(".", $filename)[1];
    }

    public function createPage($book_id, $content){
        $page = new Page();
        $page->book_id = $book_id;
        $page->content = $content;
        return $page->save();
    }

    public function createBook($request){
        $book = new Book();
        $book->name = $request->name;
        $book->author = $request->author;
        $book->description = $request->description;
        $book->folder_id = $request->folder_id;
        $book->user_id = $request->user_id;
        $book->save();
        return $book;
    }

    public function converter(){

        $API = new Convertio("4a05d9904fc8070fa1d0e165d00bf3df");
        // You can obtain API Key here: https://convertio.co/api/
        $text = $API->start(storage_path('docs').'/'.$this->filename, 'txt')
            ->wait()
            ->fetchResultContent()
            ->result_content;
        $API->delete();
        return $text;
    }

    public function cutToPages($text, $book){
        preg_match_all("/.*?[.?!](?:\s|$)/s", $text, $items);

        $n = 1800;
        foreach ($items[0] as $item){
            if (!isset($page)){
                $page = $item;
            }else{
                if(strlen($page . $item) < $n){
                    $page = $page . $item;
                } else {
                    $length = strlen($page);
                    $page_in_db = $this->createPage($book->id, $page);
                    unset($page);
                    $n = $length + 1;
                }
            }
        }

        if(isset($page) && strlen($page . $item) < $n) {
            $page_in_db = $this->createPage($book->id, $page);
            unset($page);
        }

        return $page_in_db . '<br>';
    }
}
