<?php
namespace App\Http\Components\Schemas;

/**
 * @OA\Schema(
 *     schema="Book",
 *     type="object",
 *     required={"title", "author", "release_date"},
 *     @OA\Property(property="title", type="string", example="Idiot"),
 *     @OA\Property(property="author", type="string", example="F.M. Dostoevsky"),
 *     @OA\Property(property="release_date", type="integer", example="1896-01-01"),
 * )
 */
class Book{
    //
}