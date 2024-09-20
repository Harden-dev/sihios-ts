<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class ForgortPasswordController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="PasswordReset",
     *     type="object",
     *     title="Réinitialisation de Mot de Passe",
     *     required={"token", "email", "password", "password_confirmation"},
     *     @OA\Property(
     *         property="token",
     *         type="string",
     *         description="Token de réinitialisation"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         description="Adresse e-mail de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="password",
     *         type="string",
     *         description="Nouveau mot de passe"
     *     ),
     *     @OA\Property(
     *         property="password_confirmation",
     *         type="string",
     *         description="Confirmation du nouveau mot de passe"
     *     )
     * )
     */

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     tags={"Réinitialisation de Mot de Passe"},
     *     summary="Envoyer un lien de réinitialisation de mot de passe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="utilisateur@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lien de réinitialisation envoyé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur lors de l'envoi du lien",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    //
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email'], 200)
            : response()->json(['error' => 'Unable to send reset link'], 400);
    }

    /**
     * @OA\Post(
     *     path="/reset-password",
     *     tags={"Réinitialisation de Mot de Passe"},
     *     summary="Réinitialiser le mot de passe",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="token_de_réinitialisation"),
     *             @OA\Property(property="email", type="string", example="utilisateur@example.com"),
     *             @OA\Property(property="password", type="string", example="nouveauMotDePasse123"),
     *             @OA\Property(property="password_confirmation", type="string", example="nouveauMotDePasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur lors de la réinitialisation du mot de passe",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     )
     * )
     */

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->email)->first();
            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'Password reset successfully',
                'token' => $token
            ], 200);
        } else {
            return response()->json(['error' => 'Unable to reset password'], 400);
        }
    }
}
