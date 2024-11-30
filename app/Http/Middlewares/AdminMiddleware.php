<?php 

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {   
        $token = $request->header('Authorization');
        $user = User::where('token', $token)->first();
        
        if (!$user){
            return response()->json(['message' => 'Unathorized'], 401);
        }

        if (!$user->is_admin){ 
            return response()->json(['message' => 'Not enough permissions', 403]);
        }
        
        return $next($request);
    }
}