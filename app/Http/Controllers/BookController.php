<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\Book;
use App\Models\User; 
use App\Models\Reservation;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="Books",
 *     description="Operations with books"
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
            $book = Book::find($id);
            if(!$book){
                return response()->json(['message' => 'Book not found', 404]);
            }
            return response()->json($book);
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
        $books = Book::where("title", "LIKE", "%{$query}%")
                ->orWhere("author", "LIKE", "%{$query}%")
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
            $book = Book::find($id);
            if (!$book){
                return response()->json(['message' => 'Book not found', 404]);
            }
            $book->update($request->all());
            return response()->json($book);            
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
     *     @OA\Response (response="201", description="Book reserved"),
     *     @OA\Response(response="400", description="Book is already reserved"),
     *     @OA\Response(response="401", description="Token not provided"),
     *     @OA\Response(response="404", description="Book not found"), 
     *     @OA\Response(response="500", description="Server error")
     * )
     */
    public function reserve($id, Request $request)
    {  
        try {

            $token = $request->header('Authorization');

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 401);
            }

            $user = User::where('token', $token)->first();
    
            if (!$user) {
                return response()->json(['message' => 'User  not found'], 404);
            }

            $book = Book::find($id);
            if ($book->is_reserved) {
                return response()->json(['message' => 'Book is already reserve'], 400);
            }

            $reservation = Reservation::create([
                'book_id' => $book->id,
                'user_id' => $user->id, 
            ]);

            $book->is_reserved = true;
            $book->save();
            return response()->json($book, 201);

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
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="500", description="Server error"),
     * )
     */
    public function return($id, Request $request)
    {
        try {

            $token = $request->header('Authorization');
            
            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 401);
            }

            $user = User::where('token', $token)->first();
    
            if (!$user) {
                return response()->json(['message' => 'User  not found'], 404);
            }

            $book = Book::find($id);
            if (!$book) {
                return response()->json(['message' => 'Book not found'], 404);
            }

            if (!$book->is_reserved) {
                return response()->json(['message' => 'Book is not reserved'], 400);
            }

            $reservation = Reservation::where('book_id', $book->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$reservation) {
                return response()->json(['message' => 'No reservation found for this user and book'], 404);
            }

            $reservation->delete();
            $book->is_reserved = false;
            $book->save();

            return response()->json($book);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
