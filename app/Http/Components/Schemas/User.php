<?php
namespace App\Http\Components\Schemas;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", example="Alesha"),
 *     @OA\Property(property="email", type="string", example="alesha12@gmail.com"),
 *     @OA\Property(property="password", type="string", example="supersecretpassword123"),
 * )
 */
class User{
    //
}