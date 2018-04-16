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

        $response = $this->formatResponse('success', null, $this->data);
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
        $folder = Folder::create($request->all());

        $response = $this->formatResponse('success', null, $folder);
        return response($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Folder $folder)
    {
        $folder = Folder::find($folder->id);

        $response = $this->formatResponse('success', null, $folder);
        return response($response, 200);
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

        $response = $this->formatResponse('success', null, $folder);
        return response($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Folder $folder)
    {

        $ifFolder = $folder->where('id', $folder->id)->get()->first();

        if($this->ifUser($folder) == true){
            if (!$ifFolder == null){
                $folder->delete();
                return response()->json([
                    'msg' => 'Deleted'
                ], 200);
            }
            $response = $this->formatResponse('error', 'This folder does not exist');
            return response($response, 200);
        }
        $response = $this->formatResponse('error', 'You cannot delete this folder');
        return response($response, 200);
    }

    public function defaultFolder($user_id){
        $folder = new Folder();
        $folder->name = 'Новое';
        $folder->user_id = $user_id;
        $folder->save();
    }
}
