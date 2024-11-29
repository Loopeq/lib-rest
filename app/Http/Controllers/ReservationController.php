<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ReservationController extends Controller
{
    public function index()
    {
        return response()->json(Reservation::with('book')->get());
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
                'book_id' => 'required|exists:books,id',
                'user_id' => 'required|string',
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
            ], 404);
        } catch (ValidationError $e){ 
            return response()->json([
                "error" => "Validation error",
                "messages" => $e->validator->errors(),
            ], 400);
        } catch (\Exception $e){ 
            return response()->json([
                "error" => "Server error",
                "message" => $e->getMessage(),
            ], 500)
        }
    }

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
            ], 500)
        }

    }

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
