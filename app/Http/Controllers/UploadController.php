<?php

namespace App\Http\Controllers;

use App\Book;
use App\DOCXText;
use App\Http\Requests\UploadRequest;
use App\Page;
use App\PDFText;
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
            $docPDF = new PDFText();
            $docPDF->setFilename(storage_path('docs') . '/' . $this->filename);
            $docPDF->decodePDF();

            $text = $docPDF->output();

            $c = 'ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ssssssssssernioessssssssssssssssssssssssssssssssssssssssssssssssssssssssernioesssssssssssssssssssssss. ';

            preg_match_all("/.*?[.?!](?:\s|$)/s", $text, $items);

            $n = 1800;

            foreach ($items[0] as $item){
                if (!isset($page)){
                    $page = $item;
                }else{
                    if(strlen($page . $item) < $n){
                        $page = $page . $item;
                    }
                }

            }
            echo $page . '<br>';
//            return $page;


//
//            $quantity = strlen($text);
//            $page = '';
//            while ($n != $quantity && $n < $quantity){
//
//                $offer = substr($text, $n,1800);
//
//                $page = $page . $offer;
//                $quantity = strlen($page);
//            };
//
//
//            var_dump($items);

//            dd(implode("<br/>",$split));


//            while ($n != $quantity && $n < $quantity){
//                $page = substr($text, $n,1800);
//                $n+=1801;
//                var_dump($page);
//            };
//
//            dd(strlen(''));
//            dd($docPDF->output());

//            $this->createBook($request->folder_id, $request->name, $docPDF->output(), $request->author);
        }
        if ($this->getExtension($this->filename) == 'docx') {
            $docText = new DOCXText();
//            dd($docText->readDocx(storage_path('docs').'/'.$this->filename));
            $this->createBook($request->folder_id, $request->name, $docText->readDocx(storage_path('docs').'/'.$this->filename), $request->author);
        }
    }

    function getExtension ($filename)
    {
        return explode(".", $filename)[1];
    }

    public function createBook($book_id, $content){
        $page = new Page();
        $page->book_id = $book_id;
        $page->content = $content;
        return $page->save();
    }
}
