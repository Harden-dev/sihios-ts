<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{

    // public function getSubscribers()
    // {
    //     // Ajoutez une authentification ou une vérification d'API key ici

    //     $subscribers = NewsletterSubscriber::where('is_active', true)
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(10);

    //     return response()->json($subscribers);
    // }

    /**
     * @OA\Post(
     *     path="/newsletter/subscribe",
     *     tags={"Newsletter"},
     *     summary="S'abonner à la newsletter",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="utilisateur@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Abonnement réussi",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, jeton manquant ou invalide",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function subscribe(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
        ]);

        $subscriber = new NewsletterSubscriber([
            'email' => $request->input('email'),
        ]);

        $subscriber->save();

        return response()->json(['message' => 'Abonnement réussi'], 201);
    }

    /**
     * @OA\Post(
     *     path="/newsletter/unsubscribe",
     *     tags={"Newsletter"},
     *     summary="Se désabonner de la newsletter",
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="utilisateur@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Désabonnement réussi",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, jeton manquant ou invalide",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function unsubscribe(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:newsletter_subscribers,email',
        ]);

        $subscriber = NewsletterSubscriber::where('email', $validatedData['email'])->first();
        $subscriber->is_active = false;
        $subscriber->save();

        return response()->json(['message' => 'Désabonnement réussi']);
    }

    /**
     * @OA\Schema(
     *     schema="NewsletterSubscriber",
     *     type="object",
     *     title="Abonné à la Newsletter",
     *     required={"id", "email"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique de l'abonné"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         description="Adresse e-mail de l'abonné"
     *     )
     * )
     */
}
