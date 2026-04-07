<?php
namespace App\Api\v1\Controllers;

use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use App\Services\Database\DatabaseService;

class ApiLoginController extends Controller
{

    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

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

            // $token = $user->createToken('LaravelPasseportToken')->accessToken();

            return response()->json(['message' => 'Authentification réussie', 'user' => $user], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function importCsvDuplicate(Request $request){
        try{
            $lines = $request->lines;
            $this->databaseService->importCsvDuplicate($lines);
            return response()->json(['message' => 'Import réussie'], 200);
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}