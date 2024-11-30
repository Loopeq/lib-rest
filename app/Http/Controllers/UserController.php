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
     *     tags={"User"},
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
     *     ),
     *     @OA\Response(response="500", 
     *                  description="Server Error")
     *  )
     */
    public function register(Request $request)
    {
        try{
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
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Server error",
                "message" => $e->getMessage(),
            ], 500);
        }   
    }

    /**
     * @OA\Get(
     *     path="/api/user/me",
     *     summary="Get user me",
     *     tags={"User"},
     *     @OA\Response(
     *         response="200",
     *         description="User found",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Token not provided",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *     ),
     *     @OA\Response(response="500", 
     *                  description="Server Error")
     *  )
     */
    public function me(Request $request){ 
        try{
            $token = $request->header('Authorization');
            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 401);
            }
            $user = User::where('token', $token)->first();
            if (!$user) {
                return response()->json(['message' => 'User  not found',], 404);
            }

            return response() -> json(['message' => 'User found', 'user' => $user], 200);
        } catch (\Exceptions $e){
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }

    }

    private function generateToken(): string{
        return substr(bin2hex(random_bytes(16)), 0, 16);
    }
    
}   
