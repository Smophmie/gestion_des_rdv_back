<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::all();
        return $users;
    }

    /**
     * Store a newly created user in storage.
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:60',
            'firstname' => 'required|max:60',
            'email' => [
                'required', 
                'email',
                'unique:users,email'
            ],
            'password' => [
                'required', 
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]+$/',                
                'confirmed'
            ],
            'password_confirmation' => 'required',
            'profession' => 'required|in:secretary,practitioner',
        ]);
        try {
            $user = User::create($validatedData);
    
            return response()->json([
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'La création de compte n\'a pas fonctionné!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        return $user;
    }

    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => "L'e-mail ou le mot de passe est incorrect.",
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if(auth('sanctum')->check()){
                auth()->user()->tokens()->delete();
             }

            return response()->json([
                'status' => true,
                'message' => 'Utilisateur connecté.',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'name' => $user->name,
                'firstname' => $user->firstname,
                'profession' => $user->profession
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer plus tard.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();

            return response()->json([
                'status' => true,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Utilisateur non déconnecté',
        ], 401);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        $user->delete();
        return 'L\'utilisateur a été supprimé';
    }
}
