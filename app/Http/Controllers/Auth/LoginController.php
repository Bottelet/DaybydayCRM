<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function apiLogin(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            if ($this->attemptLogin($request)) {
                $user = $this->guard()->user();
                return response()->json(['success' => true, 'message' => 'connexion', 'user' => $user]);
            }

            return response()->json(['success' => false, 'message' => 'Authentication failed.Please try again.']);
        }
        catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Login failed ','errors' => $e->errors()]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Login failed: '.$e->getMessage()]);
        }

    }

}
