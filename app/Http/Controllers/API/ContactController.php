<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{/**
 * @OA\Schema(
 *     schema="Contact",
 *     type="object",
 *     title="Contact",
 *     required={"nom_prenom", "email", "message"},
 *     @OA\Property(
 *         property="nom_prenom",
 *         type="string",
 *         description="Nom et prénom de la personne qui envoie le message"
 *     ),
 *     @OA\Property(
 *         property="tel",
 *         type="string",
 *         description="Numéro de téléphone de la personne"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Adresse e-mail de la personne"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Message à envoyer"
 *     )
 * )
 */





    /**
 * @OA\Post(
 *     path="/contact",
 *     tags={"Contact"},
 *     summary="Envoyer un message de contact",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="nom_prenom", type="string", example="Jean Dupont"),
 *             @OA\Property(property="tel", type="string", example="0123456789"),
 *             @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
 *             @OA\Property(property="message", type="string", example="Bonjour, j'aimerais avoir des informations.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Message envoyé avec succès",
 *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation",
 *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
 *     )
 * )
 */
public function store(Request $request)
{
    $validatedData = $request->validate([
        'nom_prenom' => 'required',
        'email' => 'required|email',
        'message' => 'required',
    ]);

    $contact = new Contact([
        'nom_prenom' => $request->input('nom_prenom'),
        'tel' => $request->input('tel'),
        'email' => $request->input('email'),
        'message' => $request->input('message'),
    ]);

    $contact->save();

    // Envoi de l'email
    Mail::to('sihiotsinfo@gmail.com')
        ->cc('info@sihiots.net')
        ->send(new ContactMail($contact));

    return response()->json(['success' => $contact], 200);
}
}
