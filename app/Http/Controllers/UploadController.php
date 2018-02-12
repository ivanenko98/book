<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\UploadRequest;
use App\Page;
use Convertio\Convertio;
use Convertio\Exceptions\APIException;
use Convertio\Exceptions\CURLException;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;


class UploadController extends Controller
{
    public $filename;

    public $path;

    public $page_in_db;

    public function upload(Request $request)
    {

//        ini_set('max_execution_time', 0);

        if ($request->hasFile('file')) {
            $request->file('file')->store('docs');
        } else {
            return response()->json([
                'status' => false,
                'msg' => 'Error'
            ]);
        }

        $this->filename = $request->file->hashName();

        $this->path = Storage::path('docs').'/'.$this->filename;

        $book = $this->createBook($request);

//        dd($book);

        switch ($this->getExtension($this->filename)) {
            case "pdf":
                $text = $this->pdfToTxt();
                break;
            case "docx":
                $text = $this->docxToTxt();
                break;
        }

        if (isset($text)){
            $this->cutToPages($text, $book);
        }

        return response()->json($book, 200);
    }

    function getExtension ($filename)
    {
//        return explode(".", $filename)[1];
        $filename = substr($filename, strpos($filename, ".") + 1);
//        dd($filename);
        return $filename;
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

    public function pdfToTxt(){
        $reader = new \Asika\Pdf2text;
        dd($this->path);
        $text = $reader->decode($this->path);
        dd($text);
    }


    private function docxToTxt(){

        $content = '';

        $zip = zip_open($this->path);

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }// end while

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
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
                    $this->page_in_db = $this->createPage($book->id, $page);
                    unset($page);
                    $n = $length + 1;
                }
            }
        }

        if(isset($page) && strlen($page . $item) < $n) {
            $this->page_in_db = $this->createPage($book->id, $page);
            unset($page);
        }

        return $this->page_in_db . '<br>';
    }
}
