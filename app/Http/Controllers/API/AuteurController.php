<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Auteur;
use Illuminate\Http\Request;


class AuteurController extends Controller
{

    /**
     * @OA\Schema(
     *     schema="Auteur",
     *     type="object",
     *     title="Auteur",
     *     required={"id", "name"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique de l'auteur"
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Nom de l'auteur"
     *     )
     * )
     */

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/auteur",
     *     tags={"Auteurs"},
     *     summary="Obtenir la liste des auteurs",
     * security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Une liste d'auteurs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Auteur")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $auteurs = Auteur::query()->paginate($perPage);
        return response()->json(['Auteurs' => $auteurs]);
    }

    /**
     * @OA\Post(
     *     path="/auteur",
     *     tags={"Auteurs"},
     *     summary="Créer un nouvel auteur",
     *     security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Nom de l'auteur")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Auteur créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Auteur")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, jeton manquant ou invalide",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
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
        $request->validate(
            [
                'name' => 'required'
            ]
        );

        try {
            $auteurs = Auteur::create([
                'name' => $request->name
            ]);

            $authToken = $request->bearerToken(); // Récupère le token Bearer


            return response()->json([
                "success" => "auteur enregistré avec succès",
                "datas" => $auteurs
            ], 201)

                ->header('Content-Type', 'application/json')
                ->header('Authorization', 'Bearer ' . $authToken)
                ->header('Accept', 'application/json');
        } catch (\Exception $th) {
            return response()->json(["error" => "l'enregistrement a échoué, veuiler réessayer"], 400);
        }
    }

    public function update(Request  $request, $id)
    {
        $auteurs = Auteur::findOrFail($id);
        if (!$auteurs) {
            return response()->json(["error" => "auteur non trouvé"], 404);
        }
        $request->validate([
            'name' => 'required'
        ]);
        try {
            $auteurs->name = $request->name;
            $auteurs->save();
            return response()->json(["success" => "auteur modifié avec succès", "auteurs"=>$auteurs], 200);
        } catch (\Exception $th) {
            return response()->json(["error" => "la modification a échoué, veuillez réessayer"]);
        }
    }

    public function destroy($id)
    {
        $auteurs = Auteur::findOrFail($id);
        if (!$auteurs) {
            return response()->json(["error" => "auteur non trouvé"], 404);
        }
        try {
            $auteurs->delete();
            return response()->json(["success" => "auteur supprimé avec succès",], 200);
        } catch (\Exception $th) {
            return response()->json(["error" => "la suppression a échoué, veuillez réessayer"]);
        }
    }
}
