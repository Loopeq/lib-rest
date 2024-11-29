<?php

namespace App\Http\Controllers;
use App\Models\Book;
use App\Models\Reservation;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="Reservation",
 *     description="Operations with reservation",
 * )
 */
class ReservationController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/reservations",
     *     summary="Get all reservations",
     *     tags={"Reservation"},
     *     @OA\Response(response="200", description="A list of reservations")
     * )
     */
    public function index()
    {
        return response()->json(Reservation::with('book')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/reservations",
     *     summary="Create a new reservation",
     *     tags={"Reservation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *     @OA\Response(response="201", description="Reservation created"),
     *     @OA\Response(response="400", description="Book already reserved"),
     *     @OA\Response(response="404", description="Book or User not found"),
     *     @OA\Response(response="500", description="Server error")
     * )
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'book_id' => 'required|exists:books,id',
                'user_id' => 'required|exists:users,id',
            ]);

            $book = Book::findOrFail($request->book_id);
            if ($book->is_reserved) {
                return response()->json(['message' => 'Book is already reserved'], 400);
            }
            $reservation = Reservation::create($request->all());
            $book->is_reserved = true;
            $book->save();

            return response()->json($reservation, 201);
        } catch (ModelNotFoundException $e){
            return response()->json([
                "error" => "Book to reservation not found",
                "messages" => $e->getMessage(),
            ], 400);
        } catch (ValidationException $e){ 
            return response()->json([
                "error" => "Validation error",
                "messages" => $e->validator->errors(),
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
     *     path="/api/reservations/{id}",
     *     summary="Get a reservation by ID",
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Reservation details"),
     *     @OA\Response(response="404", description="Reservation not found"),
     *     @OA\Response(response="500", description="Server error"),
     * )
     */
    public function show(string $id)
    {
        try{ 
            $reservation = Reservation::with('book')->findOrFail($id);
            return response()->json($reservation);
        } catch(ModelNotFoundException $e){ 
            return response()->json([
                "error" => "Reservation not found",
                "messages" => $e->getMessage(),
            ], 404);   
        } catch (\Exception $e){ 
            return response()->json([
                "error" => "Server error",
                "message" => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * @OA\Delete(
     *     path="/api/reservations/{id}",
     *     summary="Delete a reservation by ID",
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Reservation deleted"),
     *     @OA\Response(response="404", description="Reservation not found"),
     *     @OA\Response(response="500", description="Serve error")
     * )
     */
    public function destroy(string $id)
    {
        try{ 
            $reservation = Reservation::findOrFail($id);
            $book = Book::findOrFail($reservation->book_id);
            $book->is_reserved = false;
            $book->save();
            $reservation->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Reservation or Book not found',
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
