<?php

namespace App\Api\v1\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;



class ConnexionController extends ApiController{
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)
        || !$user->canChangeRole()) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

    
        return response()->json([
            'user' => $user
        ], 200);
    }
}

?>