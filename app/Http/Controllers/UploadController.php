<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
                $file->move(storage_path('docs'), time().'_'.$file->getClientOriginalName());
                $this->filename = time().'_'.$file->getClientOriginalName();
            }
        }
        return "Успех";
    }
}
