<?php

namespace App\Http\Controllers;

use App\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $data;

    public function index()
    {
        $user = Auth::user();
        $folders = Folder::where('user_id', $user->id)->get();

        foreach ($folders as $folder) {
            $this->data[] = [
                'id' => $folder->id,
                'name' => $folder->name,
                'user_id' => $folder->user_id,
                'created_at' => $folder->created_at,
                'updated_at' => $folder->updated_at,
                'books' => $folder->books
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
        $folder = Folder::create($request->all());
        return response()->json($folder, 201);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Folder $folder)
    {
        $folder = Folder::find($folder->id);
        return response()->json($folder, 200);
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
    public function update(Request $request, Folder $folder)
    {
        $folder->update($request->all());
        return response()->json($folder, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Folder $folder)
    {
        $folder->books()->delete();
        foreach ($folder->books as $book){
            $book->pages()->delete();
        }
        $folder->books()->delete();
        $folder->delete();

        return response()->json([
            'msg' => 'Deleted'
        ], 200);
    }

    public function defaultFolder($user_id){
        $folder = new Folder();
        $folder->name = 'Новое';
        $folder->user_id = $user_id;
        $folder->save();
    }
}
