<?php
namespace App\Api\v1\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ApiLoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        info('Email: ' . $request->email);
        info('Password: ' . $request->password);

        try {
            $user = User::where('email', $request->email)->firstOrFail();
            
            if (!\Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Echec de l\'authentification'], 500);
            }

            return response()->json(['message' => 'Authentification réussie', 'user' => $user], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}