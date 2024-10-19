<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Librairie;
use App\Rules\AllowedFileType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Log;
use Storage;


class LibrairieController extends Controller
{

    /**
     * @OA\Schema(
     *     schema="Librairie",
     *     type="object",
     *     title="Librairie",
     *     required={"id", "title", "file_path", "mime_type", "size"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique de la librairie"
     *     ),
     *     @OA\Property(
     *         property="title",
     *         type="string",
     *         description="Titre de la librairie"
     *     ),
     *     @OA\Property(
     *         property="file_path",
     *         type="string",
     *         description="Chemin du fichier téléchargé"
     *     ),
     * @OA\Property(
     *         property="file_img",
     *         type="string",
     *         description="Chemin de l'image téléchargé"
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
     *     ),
     *     @OA\Property(
     *         property="auteurs",
     *         type="array",
     *         @OA\Items(type="integer"),
     *         description="Liste des identifiants des auteurs associés à la librairie"
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
     *     path="/librairie",
     *     tags={"Librairies"},
     *     summary="Obtenir la liste des librairies",
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
     *         description="Une liste de librairies",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Librairie")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);


        $librairies = Librairie::query()->OrderByDesc('created_at')->paginate($perPage);
        $librairies->load('auteurs');

        foreach ($librairies as $librairie) {
            $librairie->file_url = asset('storage/librairie/' . $librairie->file_path);
            $librairie->image_url = asset('storage/librairie/' . $librairie->file_img);
        }
        return response()->json($librairies);
    }

    /**
     * @OA\Post(
     *     path="/api/librairies",
     *     summary="Créer une nouvelle librairie",
     *     tags={"Librairies"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", maxLength=255, description="Titre de la librairie"),
     *                 @OA\Property(property="categorie_id", type="integer", description="ID de la catégorie"),
     *                 @OA\Property(
     *                     property="auteurs",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Liste des IDs des auteurs"
     *                 ),
     *                 @OA\Property(property="file", type="string", format="binary", description="Fichier principal (PDF, etc.)"),
     *                 @OA\Property(property="file_img", type="string", format="binary", description="Image de couverture")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Librairie créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="librairie", ref="#/components/schemas/Librairie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        // Validation des fichiers
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'categorie_id'  => 'required',
            'auteurs' => 'required|array',
            'auteurs.*:auteurs,id',
            'file' => ['required', 'file', new AllowedFileType],
            'file_img' => ['required', 'image',  'max:20480'], // Validation pour l'image
        ]);

        try {
            // Traitement du fichier principal
            $file = $request->file('file');
            $path = $file->store('', 'librairie');

            // Traitement de l'image
            if ($request->hasFile('file_img')) { // Vérification si le fichier image est présent
                $fileImg = $request->file('file_img');
                $pathImg = $fileImg->store('', 'librairie');
            } else {
                throw new Exception('Image file is required.'); // Gérer l'absence de fichier image
            }
            $librairie = Librairie::create([
                'title' => $request->title,
                'categorie_id' => $request->categorie_id,
                'file_img' => $pathImg,
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
            Log::info($librairie);

            $librairie->auteurs()->attach($request->auteurs);

            $librairie->load('auteurs');
            $librairie->file_url = asset('storage/librairie/' . $path);
            $librairie->image_url = asset('storage/librairie/' . $pathImg);

            return response()->json([
                'librairie' => $librairie,
                //'auteurs' => $auteurs,

            ], 201);
        } catch (Exception $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }





    public function showFile($id)
    {
        $file = Librairie::findOrFail($id);

        return response()->file(storage_path('app/public/librairie/' . $file->file_path));
    }


    /**
     * @OA\Get(
     *     path="/librairie/detail/{id}",
     *     tags={"Librairies"},
     *     summary="Obtenir les détails d'une librairie par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la librairie à récupérer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la librairie récupérés avec suc
     * cès",
     *         @OA\JsonContent(ref="#/components/schemas/Librairie")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Librairie non trouvée",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function showInfo($id)
    {
        $librairie = Librairie::findOrFail($id);
        if(!$librairie){
            return response()->json(["error"=>"librairie non trouvé"]);
        }
        $librairie->load('auteurs');

        return response()->json(['librairie' => $librairie,], 200);
    }


    /**
     * @OA\Put(
     *     path="/api/librairies/{id}",
     *     tags={"Librairies"},
     *     summary="Mettre à jour une librairie existante",
     *     description="Met à jour les détails d'une librairie, y compris les fichiers associés et les auteurs",
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la librairie à mettre à jour",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", description="Nouveau titre de la librairie"),
     *                 @OA\Property(property="categorie_id", type="integer", description="Nouvel ID de la catégorie"),
     *                 @OA\Property(
     *                     property="auteurs",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Nouveaux IDs des auteurs associés"
     *                 ),
     *                 @OA\Property(property="file", type="string", format="binary", description="Nouveau fichier principal (optionnel)"),
     *                 @OA\Property(property="file_img", type="string", format="binary", description="Nouvelle image de couverture (optionnelle)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Librairie mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mise à jour réussie"),
     *             @OA\Property(
     *                 property="librairie",
     *                 ref="#/components/schemas/Librairie"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Librairie non trouvée",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Librairie non trouvée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\AdditionalProperties(
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Une erreur est survenue lors de la mise à jour")
     *         )
     *     )
     * )
     */

     public function update(Request $request, $id)
     {
         // Log pour vérifier l'ID et les données de la requête
         Log::info('Attempting to update librairie:', ['id' => $id, 'data' => $request->all()]);

         $librairie = Librairie::find($id);
         if (!$librairie) {
             return response()->json(['error' => 'Librairie not found'], 404);
         }

         // Validation des données entrantes
         $validator = $request->validate([
             'title' => 'sometimes|string|max:255',
            'categorie_id' => 'sometimes|integer|exists:categories,id',
            // 'auteurs' => 'sometimes|array',
            // 'auteurs.*' => 'exists:auteurs,id',
            // 'file' => ['nullable', 'file', new AllowedFileType],
            // 'file_img' => ['nullable', 'image', 'max:20480'],
         ]);

         try {
             // Mise à jour des champs simples
             $librairie->fill($request->only(['title', 'categorie_id']));

             // Gestion du fichier principal (optionnel)
             if ($request->hasFile('file')) {
                 if ($librairie->file_path) {
                     Storage::disk('librairie')->delete($librairie->file_path);
                 }
                 $file = $request->file('file');
                 $path = $file->store('', 'librairie');
                 $librairie->file_path = $path;
                 $librairie->mime_type = $file->getClientMimeType();
                 $librairie->size = $file->getSize();
             }

             // Gestion du fichier image (optionnel)
             if ($request->hasFile('file_img')) {
                 if ($librairie->file_img) {
                     Storage::disk('librairie')->delete($librairie->file_img);
                 }
                 $fileImg = $request->file('file_img');
                 $pathImg = $fileImg->store('', 'librairie');
                 $librairie->file_img = $pathImg;
             }

             // Mise à jour des auteurs
             if ($request->has('auteurs') && is_array($request->auteurs)) {
                 $librairie->auteurs()->sync($request->auteurs);
             }

             $librairie->save();

             // Charger les relations et générer les URLs
             $librairie->load('auteurs');
             $librairie->file_url = asset('storage/librairie/' . $librairie->file_path);
             $librairie->image_url = asset('storage/librairie/' . $librairie->file_img);

             return response()->json([
                 'message' => 'Mise à jour réussie',
                 'librairie' => $librairie,
             ], 200);
         } catch (ValidationException $e) {
             return response()->json(['errors' => $e->errors()], 422);
         } catch (\Exception $e) {
             \Log::error('Error updating librairie:', ['error' => $e->getMessage()]);
             return response()->json(['error' => 'Mise à jour échouée'], 500);
         }
     }

    // public function update(Request $request, $id)
    // {
    //        // Recherche de l'élément existant
    //        $librairie = Librairie::findOrFail($id);
    //     // Validation des fichiers

    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'categorie_id' => 'required',
    //         'auteurs' => 'required|array',
    //         'auteurs.*:auteurs,id',
    //         'file' => ['nullable', 'file', new AllowedFileType], // file peut être nullable si pas de changement
    //         'file_img' => ['nullable', 'image', 'max:20480'], // file_img peut aussi être nullable
    //     ]);
    //     Log::info($request->all());

    //     try {
         

    //         // Traitement du fichier principal
    //         if ($request->hasFile('file')) {
    //             $file = $request->file('file');
    //             $path = $file->store('', 'librairie');
    //             $librairie->file_path = $path;
    //             $librairie->mime_type = $file->getClientMimeType();
    //             $librairie->size = $file->getSize();
    //         }

    //         // Traitement de l'image
    //         if ($request->hasFile('file_img')) { // Vérification si le fichier image est présent
    //             $fileImg = $request->file('file_img');
    //             $pathImg = $fileImg->store('', 'librairie');
    //             $librairie->file_img = $pathImg;
    //         }

    //         // Mise à jour des autres champs
    //         $librairie->title = $request->title;
    //         $librairie->categorie_id = $request->categorie_id;

    //         $librairie->save();

    //         // Mise à jour des auteurs
    //         $librairie->auteurs()->sync($request->auteurs);

    //         // Recharger les relations
    //         $librairie->load('auteurs');
    //         $librairie->file_url = asset('storage/librairie/' . $librairie->file_path);
    //         $librairie->image_url = asset('storage/librairie/' . $librairie->file_img);

    //         return response()->json([
    //             'librairie' => $librairie,
    //         ], 200);
    //     } catch (Exception $th) {
    //         return response()->json(['error' => $th->getMessage()], 500);
    //     }
    // }


    /**
 * @OA\Get(
 *     path="/librairie/filter-by-category",
 *     summary="Filtrer les livres par catégorie",
 *     description="Récupère une liste de livres, optionnellement filtrée par catégorie",
 *     tags={"Librairies"},
 *     @OA\Parameter(
 *         name="categorie_id",
 *         in="query",
 *         description="ID de la catégorie pour filtrer les livres",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des livres",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="title", type="string"),
 *                 @OA\Property(property="categorie_id", type="integer"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Aucun livre trouvé"
 *     )
 * )
 */
     public function filterByCategory(Request $request)
     {
        $categorieId = $request->query('categorie_id');
        
        $librairies = Librairie::when($categorieId, function ($query) use ($categorieId) {
            return $query->where('categorie_id', $categorieId);
        })->get();

        return response()->json($librairies);
     }


    /**
     * @OA\Delete(
     *     path="/librairie/delete/{id}",
     *     tags={"Librairies"},
     *     summary="Supprimer une librairie par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la librairie à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Librairie supprimée avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Librairie non trouvée",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur du serveur",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function destroy($id)
    {
        // Trouver la librairie à supprimer
        $librairie = Librairie::findOrFail($id);

        $librairie->auteurs()->detach(); // Detach tous les auteurs associés

        // Supprimer le fichier stocké
        $filePath = $librairie->file_path;
        Storage::disk('librairie')->delete($filePath);

        // Supprimer la librairie
        $librairie->delete();

        return response()->json(['message' => 'Librairie deleted']);
    }



    /**
     * @OA\Get(
     *     path="/librairie/{id}/download",
     *     tags={"Librairies"},
     *     summary="Télécharger un fichier de librairie par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la librairie dont le fichier doit être téléchargé",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fichier téléchargé avec succès",
     *         @OA\JsonContent(type="string")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Librairie non trouvée",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function download($id)
    {
        $file = Librairie::findOrFail($id);


        if (is_array($file->file_path)) {
            //
            $filePath = $file->file_path[0] ?? null;
        } else {
            $filePath = $file->file_path;
        }

        if (!$filePath) {
            return response()->json(['error' => 'File path not found'], 404);
        }

        return Storage::disk('librairie')->download($filePath);
    }
}
