<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcour;
use App\Rules\AllowedFileType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ParcourController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Parcour",
     *     type="object",
     *     title="Parcours",
     *     required={"id", "label", "field", "file_path", "mime_type", "size"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique du parcours"
     *     ),
     *     @OA\Property(
     *         property="label",
     *         type="string",
     *         description="Titre du parcours"
     *     ),
     *     @OA\Property(
     *         property="field",
     *         type="string",
     *         description="Domaine d'étude du parcours"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Description du parcours"
     *     ),
     *     @OA\Property(
     *         property="file_path",
     *         type="string",
     *         description="Chemin du fichier téléchargé"
     *     ),
     *     @OA\Property(
     *         property="mime_type",
     *         type="string",
     *         description="Type MIME du fichier téléchargé"
     *     ),
     *     @OA\Property(
     *         property="size",
     *         type="integer",
     *         description="Taille du fichier téléchargé en octets"
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
     *     path="/parcours/list",
     *     tags={"Parcours"},
     *     summary="Obtenir la liste des parcours",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Une liste de parcours récupérée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Parcour")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $parcours = Parcour::query()->paginate($perPage);
        foreach ($parcours as $parcour) {
            $parcour->file_url = asset('storage/parcours/' . $parcour->file_path);
        }
        return response()->json($parcours);
    }

   /**
 * @OA\Post(
 *     path="/parcours/store",
 *     tags={"Parcours"},
 *     summary="Créer un nouveau parcours",
 *     security={{"Bearer": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="label",
 *                     type="string",
 *                     example="Titre du parcours",
 *                     description="Le titre du parcours"
 *                 ),
 *                 @OA\Property(
 *                     property="field",
 *                     type="string",
 *                     example="Domaine d'étude",
 *                     description="Le domaine d'étude du parcours"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     example="Description du parcours",
 *                     description="Une description du parcours"
 *                 ),
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary",
 *                     description="Le fichier à télécharger (format binaire)"
 *                 ),
 *                 @OA\Property(
 *                     property="condition_acces",
 *                     type="array",
 *                     @OA\Items(type="string"),
 *                     description="Liste des conditions d'accès"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Parcours créé avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="label", type="string"),
 *             @OA\Property(property="field", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="file_path", type="string"),
 *             @OA\Property(property="mime_type", type="string"),
 *             @OA\Property(property="size", type="integer"),
 *             @OA\Property(
 *                 property="condition_acces",
 *                 type="array",
 *                 @OA\Items(type="string")
 *             ),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time"),
 *             @OA\Property(property="file_url", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 additionalProperties=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string"
 *             )
 *         )
 *     )
 * )
 */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'label' => 'required|string|max:255',
                'field' => 'required|string|max:255',
                'condition_acces' => 'sometimes|array',
                'condition_acces.*' => 'string',
                'description' => 'nullable|string|max:255',
                'file' => ['required', 'file', new AllowedFileType, 'max:20480'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        try {
            $file = $request->file('file');

            $allowedMimeTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            if (!in_array($file->getClientMimeType(), $allowedMimeTypes)) {
                return response()->json(['error' => 'Le fichier doit être un PDF, un document Word ou une image'], 422);
            }

            $path = $file->store('', 'parcours');
            $parcours = Parcour::create([
                'label' => $request->label,
                'field' => $request->field,
                'description' => $request->description,
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'condition_acces' => $request->condition_acces,
            ]);

            $parcours->file_url = asset('storage/parcours/' . $path);
            return response()->json($parcours, 201);
        } catch (Exception $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $file = Parcour::findOrFail($id);

        return response()->file(storage_path('app/parcours/' . $file->file_path));
    }
    /**
     * @OA\Get(
     *     path="/parcours/detail{id}",
     *     tags={"Parcours"},
     *     summary="Obtenir les détails d'un parcours par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du parcours à récupérer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du parcours récupérés avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Parcour")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parcours non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     *  */
    public function showParcoursDetail($id)
    {
        $parcours = Parcour::findOrFail($id);
        $parcours->load('conditions');

        return response()->json(["parcours" => $parcours], 200);
    }


  /**
 * @OA\Put(
 *     path="/parcours/update/{id}",
 *     tags={"Parcours"},
 *     summary="Mettre à jour un parcours existant",
 *     security={{"Bearer": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID du parcours à mettre à jour",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="label", type="string", example="Titre mis à jour du parcours"),
 *                 @OA\Property(property="field", type="string", example="Domaine d'étude mis à jour"),
 *                 @OA\Property(property="description", type="string", example="Description mise à jour du parcours"),
 *                 @OA\Property(property="file", type="string", format="binary", description="Nouveau fichier à télécharger (optionnel)"),
 *                @OA\Property(
 *                 property="condition_acces",
 *                 type="array",
 *                 @OA\Items(type="string")
 *             ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Parcours mis à jour avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Mise à jour réussie"),
 *             @OA\Property(
 *                 property="parcours",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="label", type="string"),
 *                 @OA\Property(property="field", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="file_path", type="string"),
 *                 @OA\Property(property="mime_type", type="string"),
 *                 @OA\Property(property="size", type="integer"),
 *                 @OA\Property(
 *                     property="condition_acces",
 *                     type="array",
 *                     @OA\Items(type="string")
 *                 ),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time"),
 *                 @OA\Property(property="file_url", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Parcours non trouvé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="parcours not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 additionalProperties=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string"
 *             )
 *         )
 *     )
 * )
 */


    public function update(Request $request, $id)
    {
        $parcours = Parcour::findOrFail($id);

        try {
            $validated = $request->validate([
                'label' => 'required|string|max:255',
                'field' => 'required|string|max:255',
                'condition_acces' => 'sometimes|array',
                'condition_acces.*' => 'string',
                'description' => 'sometimes|string|max:255',
                'file' => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png,gif|max:10240', // 10MB max
            ]);

            $parcours->label = $validated['label'];
            $parcours->field = $validated['field'];
            $parcours->description = $validated['description'] ?? $parcours->description;

            if (isset($validated['condition_acces'])) {
                $parcours->condition_acces = $validated['condition_acces'];
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                if ($parcours->file_path) {
                    Storage::disk('parcours')->delete($parcours->file_path);
                }

                $path = $file->store('', 'parcours');
                $parcours->file_path = $path;
                $parcours->mime_type = $file->getClientMimeType();
                $parcours->size = $file->getSize();
            }

            $parcours->save();

            return response()->json([
                'message' => 'Mise à jour réussie',
                'parcours' => $parcours
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/parcours/delete/{id}",
     *     tags={"Parcours"},
     *     summary="Supprimer un parcours par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du parcours à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parcours supprimé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parcours non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function destroy($id)
    {
        $file = Parcour::findOrFail($id);
        
        $filePath = $file->file_path;
        Storage::disk('parcours')->delete($filePath);
        $file->delete();
        return response()->json(['message' => 'parcours deleted']);
    }
}
