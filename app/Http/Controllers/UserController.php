<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="User",
 *     description="Operations with user",
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/user/register",
     *     summary="Register user",
     *     tags={"User "},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="User create successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User create successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation errors",
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $token = $this->generateToken();
        \Log::info('token: ' . $token); 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'token' => $token,
        ]);

        return response()->json(['message' => 'User create successfully', 'user' => $user], 201);   
    }

    private function generateToken(): string{
        return substr(bin2hex(random_bytes(16)), 0, 16);
    }
    
}   
