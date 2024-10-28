<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RegisterExamination;
use App\Mail\ResetMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mail;

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
        $this->middleware('auth:api', ['except' => ['login', 'register', 'ResetPasswordMail']]);
    }

    /**
     * Register a new user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Créer un nouvel utilisateur",
     *     description="Enregistre un nouvel utilisateur et envoie un e-mail de confirmation.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "phone", "job_title", "password", "password_confirmation"},
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *             @OA\Property(property="phone", type="string", example="123456789"),
     *             @OA\Property(property="job_title", type="string", example="Manager"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Jean"),
     *                 @OA\Property(property="last_name", type="string", example="Dupont"),
     *                 @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *                 @OA\Property(property="phone", type="string", example="123456789"),
     *                 @OA\Property(property="job_title", type="string", example="Manager"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="Cette adresse e-mail est déjà utilisée.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite.")
     *         )
     *     )
     * )
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

        ], $this->messageValidation());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
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

        Mail::to($user->email)->send(new RegisterExamination($user));

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
            'password' => 'required|string|min:6',
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

        if ($user->status == 'pending') {
            Auth::guard('api')->logout();
            return response()->json(['error' => 'Votre compte  est en cours en d\'approbation, contacter l\'administrateur'], 403);
        }


        if ($user->status == 'rejected') {
            Auth::guard('api')->logout();
            return response()->json(['error' => 'Impossible de vous connecter , votre compte a été rejté !'], 403);
        }

        if ($user->deleted_at) {
            Auth::guard('api')->logout();
            return response()->json(['error' => 'Votre compte  est désactivé, contacter votre administrateur'], 403);
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
            'current_password' => 'required|string|min:6',
            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                function ($attribute, $value, $fail) use ($request) {
                    // Vérifie si le nouveau mot de passe est différent de l'ancien
                    if (Hash::check($value, Auth::user()->password)) {
                        return response()->json(['error'=>'Le nouveau mot de passe doit être différent de l\'ancien mot de passe.'], 403);
                    }
                }
            ],

        ]);

        $user = Auth::guard('api')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            // Log::info('le mot de passe est incorrect');
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


    /**
     * @OA\Post(
     *     path="/reset/password/mail",
     *     summary="Réinitialiser le mot de passe de l'utilisateur",
     *     description="Cette fonction permet de réinitialiser le mot de passe d'un utilisateur et d'envoyer un nouveau mot de passe par email.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mot de passe réinitialisé et envoyé par email",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Un nouveau mot de passe a été envoyé à votre adresse e-mail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="L'utilisateur n'existe pas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Une erreur est survenue lors de la réinitialisation du mot de passe")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le champ email est obligatoire.")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Adresse e-mail de l'utilisateur",
     *         required=true,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */


    public function ResetPasswordMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {

                return response()->json(['error' => 'L\'utilisateur n\'existe pas'], 404);
            }

            if ($user->deleted_at) {

                return response()->json(['error' => 'L\'utilisateur est désactivé'], 403);
            }

            $newPassword = Str::random(8);

            $user->password = Hash::make($newPassword);
            $user->save();

            Mail::to($user->email)->send(new ResetMail($user, $newPassword));

            return response()->json(['message' => 'Un nouveau mot de passe a été envoyé à votre adresse e-mail'], 201);
        } catch (\Exception $th) {

            Log::error('Une erreur est survenue lors de la réinitialisation du mot de passe : ' . $th->getMessage());
            return response()->json(['error' => "Une erreur est survenue lors de la réinitialisation du mot de passe"], 500);
        }
    }


    protected function messageValidation()
    {
        return [

            'first_name.required' => 'Le prénom est obligatoire.',
            'first_name.string' => 'Le prénom doit être une chaîne de caractères.',
            'first_name.max' => 'Le prénom ne doit pas dépasser 255 caractères.',

            'last_name.required' => 'Le nom de famille est obligatoire.',
            'last_name.string' => 'Le nom de famille doit être une chaîne de caractères.',
            'last_name.max' => 'Le nom de famille ne doit pas dépasser 255 caractères.',

            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.string' => 'L\'adresse e-mail doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse e-mail doit être une adresse e-mail valide.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 255 caractères.',

            'job_title.required' => 'Le titre du poste est obligatoire.',
            'job_title.string' => 'Le titre du poste doit être une chaîne de caractères.',
            'job_title.max' => 'Le titre du poste ne doit pas dépasser 255 caractères.',

            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',

        ];
    }
}
