<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Requests\UploadRequest;
use App\Page;
use Convertio\Convertio;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\File;


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

        if ($this->getExtension($this->filename) == 'pdf') {
            $API = new Convertio("4a05d9904fc8070fa1d0e165d00bf3df");           // You can obtain API Key here: https://convertio.co/api/
            $text = $API->start(storage_path('docs').'/'.$this->filename, 'txt')->wait()->fetchResultContent()->result_content;
            $API->delete();
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
                            $this->createPage(1, $page);
                            unset($page);
                            $n = $length + 1;
                        }
                    }
                }

            if(isset($page) && strlen($page . $item) < $n) {
                $this->createPage(1, $page);
                unset($page);
            }

//            }

            echo $page . '<br>';


//            $this->createBook($request->folder_id, $request->name, $docPDF->output(), $request->author);
        }
        if ($this->getExtension($this->filename) == 'docx') {
            $API = new Convertio("4a05d9904fc8070fa1d0e165d00bf3df");           // You can obtain API Key here: https://convertio.co/api/
            $Text = $API->start(storage_path('docs').'/'.$this->filename, 'txt')->wait()->fetchResultContent()->result_content;
            $API->delete();
            echo $Text;
//            $this->createBook($request->folder_id, $request->name, $docText->readDocx(storage_path('docs').'/'.$this->filename), $request->author);
        }
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
}
