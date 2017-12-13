<?php

namespace App\Http\Controllers;

use App\Book;
use App\DOCXText;
use App\Http\Requests\UploadRequest;
use App\PDFText;
use Illuminate\Http\Request;
use Illuminate\Http\Testing\File;
use Spatie\PdfToText\Pdf;

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
            $docPDF->output();

            $this->createBook($request->folder_id, $request->name, $docPDF->output(), $request->author);
        }
        if ($this->getExtension($this->filename) == 'docx') {
            $docText = new DOCXText();

            $this->createBook($request->folder_id, $request->name, $docText->readDocx(storage_path('docs').'/'.$this->filename), $request->author);
        }
    }

    function getExtension ($filename)
    {
        return explode(".", $filename)[1];
    }

    public function createBook($folder_id, $name, $content, $author){
        $book = new Book();
        $book->folder_id = $folder_id;
        $book->name = $name;
        $book->content = $content;
        $book->author = $author;
        return $book->save();
    }
}
