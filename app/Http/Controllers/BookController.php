<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Book;

class BookController extends Controller
{

    public function index()
    {
        return response()->json(Book::all());
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                "title" => "required|string|max:255",
                "author" => "required|string|max:255",
                "release_date" => "required|date",
            ]);
            $book = Book::create($request->all());
            return response()->json($book, 201);
        } catch (ValidationException $e) {
            return response()->json([
                "error" => "Validation error",
                "messages" => $e->validator->errors(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Server error",
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try{
            $book = Book::findOrFail($id);
            return response()->json($book);
        } catch (ModelNotFoundException $e){
            return response()->json([
                "error" => "Book not found",
                "message" => $e->getMessage(),
            ], 404); 
        } catch (\Exception $e){
            return response()->json([
                "error" => "Server error",
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    public function find(Request $request)
    {
        $query = $request->input("query");
        $books = Book::where("title", "LIKE", "%query%")
                ->orWhere("author", "LIKE", "%query%")
                ->get();
        return response()->json($books);
    }

    public function update(Request $request, string $id)
    {
        try {
            $book = Book::findOrFail($id);
            $book.update($request->all());
            return response()->json($book);            
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Book to update not found",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Server error",
                "message" => $e->getMessage(),
            ], 500);
        }   
    }

    public function destroy(string $id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Book to remove not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
    
        }
    }

    public function reserve($id)
    {  
        try {
            $book = Book::findOrFail($id);
            if ($book->is_reserved) {
                return response()->json(['message' => 'Book is already reserve'], 400);
            }
            $book->is_reserved = true;
            $book->save();
            return response()->json($book);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Book to reserve not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function return($id)
    {
        try {
            $book = Book::findOrFail($id);
            if (!$book->is_reserved) {
                return response()->json(['message' => 'Book is not reserve'], 400);
            }
            $book->is_reserved = false;
            $book->save();
            return response()->json($book);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Book to return not found',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
