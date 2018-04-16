<?php

namespace App\Http\Controllers;

use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{

    public function index(Request $request)
    {
        $reviews = Review::where([
            ['book_id', $request->book_id],
            ['content', '!=', ['', null]],
        ])->get();

        $response = $this->formatResponse('success', null, $reviews);
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
        $data = $request->all();

        $user = Auth::user();

        if($user) {
            $data['user_id'] = $user->id;
            $data['author'] = (!empty($data['author'])) ? $data['author'] : $user->name;
        }

        $validator = Validator::make($data,[
            'rating' => 'integer|required|max:5|min:0',
            'book_id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $review = Review::create($data);

        $response = $this->formatResponse('success', null, $review);
        return response($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Review $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $review)
    {
        $review->update($request->only(['rating', 'content']));

        $response = $this->formatResponse('success', null, $review);
        return response($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Review $review
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function destroy(Review $review)
    {
        $review->delete();

        $response = $this->formatResponse('success');
        return response($response, 200);
    }
}
