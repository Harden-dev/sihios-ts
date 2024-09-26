<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Exception;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    /**
 * @OA\Schema(
 *     schema="Categorie",
 *     type="object",
 *     title="Categorie",
 *     description="Modèle de catégorie",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Identifiant unique de la catégorie"
 *     ),
 *     @OA\Property(
 *         property="label",
 *         type="string",
 *         description="Label de la catégorie"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de création de la catégorie"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de mise à jour de la catégorie"
 *     )
 * )
 */
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Récupérer toutes les catégories",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des catégories récupérée avec succès",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Categorie"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function index()
    {
        $categories = Categorie::query();
        return response()->json($categories);
    }
    /**
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Créer une nouvelle catégorie",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"label"},
     *             @OA\Property(property="label", type="string", example="Nouvelle Catégorie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Catégorie créée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Categorie")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            $request->validate([
                'label' => 'required'
            ]);


            $categories = Categorie::create([
                'label' => $request->label,
            ]);
            return response()->json(['categories' => $categories]);
        } catch (Exception $th) {
            return response()->json(["error" => "enregistrement échoué"]);
        }
    }
}
