<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Utilisez un jeton JWT pour l'authentification."
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="User",
     *     type="object",
     *     title="Utilisateur",
     *     required={"id", "first_name", "last_name", "email", "phone", "job_title"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="first_name",
     *         type="string",
     *         description="Prénom de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="last_name",
     *         type="string",
     *         description="Nom de famille de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         description="Adresse e-mail de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="phone",
     *         type="string",
     *         description="Numéro de téléphone de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="job_title",
     *         type="string",
     *         description="Poste de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Statut de l'utilisateur (ex: actif, en attente)"
     *     )
     * )
     */
    //protected $guard = 'api';

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a new user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',


        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
            'password' => Hash::make($request->password),
            'status' => 'pending',

        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);

        // $token = Auth::login($user);

        // return $this->respondWithToken($token);
    }



    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Auth"},
     *     summary="Obtenir un JWT via les identifiants fournis",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JWT généré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants non valides",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        // Check if the user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Verify the password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Mot de passe incorrect'], 401);
        }

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        if ($user->deleted_at) {
            Auth::guard('api')->logout();
            return response()->json(['error' => 'Votre est désactivé, contacter votre administrateur'], 403);
        }

        return $this->respondWithToken($token);
    }

    

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/me",
     *     tags={"Auth"},
     *     summary="Obtenir les informations de l'utilisateur connecté",
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur récupérées avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, jeton manquant ou invalide",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->user());
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */

     
    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Auth"},
     *     summary="Déconnexion de l'utilisateur",
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     )
     * )
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

   

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     /**
     * @OA\Post(
     *     path="/refresh",
     *     tags={"Auth"},
     *     summary="Rafraîchir le token JWT",
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => Auth::guard('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    /**
     * @OA\Put(
     *     path="/change-password",
     *     tags={"Auth"},
     *     summary="Changer le mot de passe de l'utilisateur",
     * security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="current_password", type="string", example="ancienMotDePasse123"),
     *             @OA\Property(property="new_password", type="string", example="nouveauMotDePasse123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="nouveauMotDePasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe changé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Mot de passe actuel incorrect",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur du serveur",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::guard('api')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            Log::info('le mot de passe est incorrect');
            return response()->json(['error' => 'le mot de passe est incorrect'], 401);
        }
        try {
            $user->password = Hash::make($request->new_password);
            $user->save();
            Log::alert('le mot de passe a été modifié avec succès');

            return response()->json(['message' => 'le mot de passe a été modifié avec succès'], 201);
        } catch (Exception $e) {
            Log::error('Erreur lors de la modification du mot de passe : ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la modification du mot de passe'], 500);
        }
    }
}
