<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Book;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="Books",
 *     description="Operations related to books"
 * )
 */
class BookController extends Controller
{

    /**
     * @OA\Get( 
     *     path="/api/books",
     *     summary="Get all books",
     *     tags={"Books"},
     *     @OA\Response(response="200", description="A list of books")
     * )
     */
    public function index()
    {
        return response()->json(Book::all());
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(response="201", description="Book created"),
     *     @OA\Response(response="400", description="Validation error"),
     *     @OA\Response(response="500", description="Server error"),
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a book by ID",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Book details"),
     *     @OA\Response(response="404", description="Book not found"),
     *     @OA\Response(response="500", description="Server error"),
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/books/search",
     *     summary="Search for books by query",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Author name or book title",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="A list of books matching the search criteria")
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input("query");
        $books = Book::where("title", "LIKE", "%query%")
                ->orWhere("author", "LIKE", "%query%")
                ->get();
        return response()->json($books);
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update a book by ID",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(response="200", description="Book updated"),
     *     @OA\Response(response="404", description="Book not found"),
     *     @OA\Response(response="500", description="Server error")
     * )
     */
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


    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a book by ID",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Book deleted"),
     *     @OA\Response(response="404", description="Book not found"),
     *     @OA\Response(response="500", description="Server error"),
     * )
     */ 
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

    /**
     * @OA\Post(
     *     path="/api/books/{id}/reserve",
     *     summary="Reserve a book by ID",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response (response="200", description="Book reserved"),
     *     @OA\Response(response="400", description="Book is already reserved"),
     *     @OA\Response(response="404", description="Book not found"), 
     *     @OA\Response(response="500", description="Server error")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/books/{id}/return",
     *     summary="Return a reserved book by ID",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Book returned"),
     *     @OA\Response(response="400", description="Book is not reserved"),
     *     @OA\Response(response="404", description="Book not found"),
     *     @OA\Response(response="500", description="Server error"),
     * )
     */
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
