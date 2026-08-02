<?php

namespace App\Http\Controllers;

use App\Services\Retrieval\Answerer;
use App\Services\Retrieval\Retriever;
use Illuminate\Http\Request;

class ChatController extends Controller
{

    public function index(){

        return view('chat.index');

    }

    public function ask(Request $request){

        $request->validate([
            'question' => 'required|string|max:1000'
        ]);

        $user = auth()->user();
        $retriever = new Retriever();
        $result    = $retriever->search($request->question , $user->id);

        $answer = null;
        $error  = null;

        try{

            $answerer = new Answerer();
            $answer   = $answerer->answer($request->question , $result);

        }catch(\Exception $e){

            $error = 'Answer generation is temporarily unavailable, but here are the relevant files found.';

        }

        return view('chat.index' ,[
            'question' => $request->question,
            'result'   => $result,
            'answer'   => $answer,
            'error'    => $error
        ]);

    }

}
