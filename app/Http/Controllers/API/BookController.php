<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;

class BookController extends Controller
{
    /*
        ##################################################
        # Book's CRUD utilities
        ##################################################    
    */

    public function getAll(Request $req)
    {
        $books = Book::all();
        return response()->json([
            'status' => "success",
            'data' => $books,
            'msg' => "",
        ],200);
    }

    public function getById(Request $req, Int $id)
    {
        if (!$id) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "id book not found",
            ], 400);  
        }

        try {
            $book = Book::all()->where('id',$id)->firstOrFail();
            return response()->json([
                'status' => "success",
                'data' => $book,
                'msg' => "",
            ],200);
        } catch (ItemNotFoundException $th) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Book with id $id not found",
            ], 400);
        }   

    }

    public function getByQuery(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'query' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400);  
        }

        $books = Book::all()->where('judul','LIKE',"%$req->query%");
        dd($books);

    }

    /* 
        Create metod on book disabled
    */
    // public function create(Request $req)
    // {
    //     $validator = Validator::make($req->all(),[
    //         ''
    //     ]);
    // }

    public function update(Request $req)
    {
        $validator = Validator::make($req->all(),[
            "id" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400);  
        }

        try {

            $book = Book::all()->where('id',$req->id)->firstOrFail();
            $book->update($req->except(['id']));

            return response()->json([
                'status' => "success",
                'data' => $book,
                'msg' => "Successfuly update book with id $book->id",
            ],200);

        } catch (ItemNotFoundException $e) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Config with id $req->id not found",
            ], 400);
        }
    }

    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'id' => "required"
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400); 
        }

        try {
            $book = Book::all()->where('id',$req->id)->firstOrFail();
            $book->delete();
            return response()->json([
                'status' => "success",
                'data' => '',
                'msg' => "Successfuly delete book with id $book->id",
            ],200);
        } catch (ItemNotFoundException $th) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Config with id $req->id not found",
            ], 400);        
        }

    }

}
