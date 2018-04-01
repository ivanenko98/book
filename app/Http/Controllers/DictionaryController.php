<?php

namespace App\Http\Controllers;

use App\DictionaryEN;
use App\DictionaryEN_UA;
use App\DictionaryUA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DictionaryController extends Controller
{
    public function listWords()
    {
        $user = Auth::user();

        $words = DictionaryEN_UA::where('user_id', $user->id)->get();

        $response = $this->arrayResponse('success', null, $words);
        return response($response, 200);
    }

    public function addToDictionary(Request $request)
    {
        $user = Auth::user();

        $word = DictionaryEN::where('word', $request->word)->first();

        if ($word == null) {
            $word = new DictionaryEN();
            $word->word = $request->word;
            $word->user_id = $user->id;
            $word->save();
        }

        $word_translate = DictionaryUA::where('word', $request->word_translate)->first();

        if ($word_translate == null) {
            $word_translate = new DictionaryUA();
            $word_translate->word = $request->word_translate;
            $word_translate->user_id = $user->id;

            $word_translate->save();
        }

        $word_relation = DictionaryEN_UA::where([
            ['en_id', $word->id],
            ['ua_id', $word_translate->id]
        ])->first();

        if ($word_relation !== null) {
            $response = $this->arrayResponse('error', 'translation is already added');
            return response($response, 200);
        }

        $word_relation = new DictionaryEN_UA();
        $word_relation->en_id = $word->id;
        $word_relation->ua_id = $word_translate->id;
        $word_relation->user_id = $user->id;
        $word_relation->save();

        $response = $this->arrayResponse('success', null);
        return response($response, 200);
    }

    public function removeFromDictionary(Request $request)
    {
        $word = DictionaryEN::where('word', $request->word)->first();

        if ($word == null) {
            $response = $this->arrayResponse('error', 'translation not found');
            return response($response, 200);
        }

        $word_translate = DictionaryUA::where('word', $request->word_translate)->first();

        if ($word_translate == null) {
            $response = $this->arrayResponse('error', 'translation not found');
            return response($response, 200);
        }

        $word_relation = DictionaryEN_UA::where([
            ['en_id', $word->id],
            ['ua_id', $word_translate->id]
        ])->first();

        if ($word_relation == null) {
            $response = $this->arrayResponse('error', 'translation not found');
            return response($response, 200);
        } else {
            $word_relation->delete();
            $word->delete();
            $word_translate->delete();

            $response = $this->arrayResponse('success', null);
            return response($response, 200);
        }
    }

    public function searchWords(Request $request)
    {
//        $user = Auth::user();
//
//        $keyword = $request->keyword;
//
//        $word = new DictionaryEN();
////
////        $word->where([
////            ['word', 'like', "%{$keyword}%"],
////            ['user_id', $user->id]
////        ]);
////
////        $word->whereHas('translateUa', function ($q) use ($keyword) {
////            $q->where(function ($q) use ($keyword) {
////                $q->where('word', 'like', "%{$keyword}%");
////            });
////        });
//
//
////
////        $words->whereHas('translateUa', function ($q) use ($keyword) {
////            $q->where(function ($q) use ($keyword) {
////                $q->where('word', 'like', "%{$keyword}%");
////            });
////        });
////
////        dd($words);
//
//        $words = $word->whereHas('translateUa', function ($q) use ($keyword) {
//            $q->where(function ($q) use ($keyword) {
//                $q->where('word', 'like', "%{$keyword}%");
//            });
//        })->orWhere('word', $keyword)->get();
//
////        dd($words);
//
//        foreach ($words as $word) {
//            $words1[] = $word;
//        }
////
//
//
////        foreach ($words as $items) {
//////            dd($items);
////            foreach ($items->translateUa as $item) {
////                dd($item->word);
////            }
////        }
//
//
//////
////        $words = DictionaryEN::where(function ($q) use ($keyword) {
////            $q->where('word', 'like', "%{$keyword}%")
////                ->whereHasMany('translateUa', function ($q) use ($keyword) {
////                    $q->where(function ($q) use ($keyword) {
////                        $q->where('word', 'like', "%{$keyword}%");
////                    });
////                });
////        })->orderBy('id', 'desc')->get();
//
//        $response = $this->arrayResponse('success', null, $words1);
//        return response($response, 200);
    }
}
