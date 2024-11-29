<?php
namespace App\Http\Components\Schemas;

/**
 * @OA\Schema(
 *     schema="Reservation",
 *     type="object",
 *     required={"book_id", "user_id"},
 *     @OA\Property(property="book_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 * )
 */
class Reservation{
    //
}