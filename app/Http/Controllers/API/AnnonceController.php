<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Rules\AllowedFileType;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PgSql\Lob;
use Storage;

class AnnonceController extends Controller
{
   /**
     * @OA\Schema(
     *     schema="Annonces",
     *     type="object",
     *     title="Annonces",
     *     required={"id", "title", "desciption", "label", "file"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique du Evenement Public"
     *     ),
     *     @OA\Property(
     *         property="label",
     *         type="string",
     *         description="Titre du Evenement Public"
     *     ),
     *     @OA\Property(
     *         property="field",
     *         type="string",
     *         description="Domaine d'étude du Evenement Public"
     *     ),
     *     @OA\Property(
     *         property="description",
     *         type="string",
     *         description="Description du Evenement Public"
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

     public function __construct()
     {
        
     }

     /**
     * @OA\Get(
     *     path="/public/event",
     *     tags={"Evenement Public"},
     *     summary="Obtenir la liste des Evenements Public",
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Annonces")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 10);
        $annonce =  Annonce::query()->OrderByDesc('created_at')->paginate($per_page);
        return response()->json($annonce);

    }

  /**
 * @OA\Post(
 *     path="/add/new/public/event",
 *     tags={"Evenement Public"},
 *     summary="Créer un nouvel Evenement Public",
 *     security={{"Bearer": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="title",
 *                     type="string",
 *                     example="Titre de l'Evenement Public",
 *                     description="Le titre de l'Evenement Public"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     example="Description du Evenement Public",
 *                     description="Une description du Evenement Public"
 *                 ),
 *                 @OA\Property(
 *                     property="file",
 *                     type="string",
 *                     format="binary",
 *                     description="Le fichier à télécharger (format binaire)"
 *                 ),
 *                 @OA\Property(
 *                     property="label",
 *                     type="array",
 *                     @OA\Items(type="string"),
 *                     description="Tableau de chaînes de caractères pour les labels de l'Evenement Public"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Evenement Public créé avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(
 *                 property="label",
 *                 type="array",
 *                 @OA\Items(type="string")
 *             ),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time"),
 *             @OA\Property(property="file_path", type="string")
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
        $validated = $request->validate(
            [
                'title'=>'required',
                'description'=>'required',
                'label' => 'sometimes|array',
                'label.*' => 'string',
                'file'=>['required', 'file', new AllowedFileType, 'max:5242880']
            ]
            );

            try {
                $file = $request->file('file');


                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif'];
                if (!in_array($file->getClientMimeType(), $allowedMimeTypes)) {
    
    
                    return response()->json(['error' => 'Le fichier doit être une image'], 422);
                }
                $path = $file->store('', 'annonce');
                File::chmod(storage_path("app/public/AnnonceFile/" . $path), 0644);
    
                $annonce = Annonce::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'label' => $request->label,
                    'file_path' => $path,
                ]);
    
                $annonce->file_url = asset('storage/AnnonceFile/' . $path);
    
                return response()->json($annonce);
            } catch (Exception $th) {
               return response()->json(["error"=>"Une erreur s'est produite veuillez le l'administrateur".$th->getMessage()]);
            }
           

            
    }



    public function showAnnonceDetailById($id)
    {
        $annonce = Annonce::findOrFail($id);
        $annonce->file_url = asset('storage/AnnonceFile/' . $annonce->file_path);
        return response()->json($annonce, 200);
    }


   /**
 * @OA\Put(
 *     path="/update/public/event/{id}",
 *     tags={"Evenement Public"},
 *     summary="Mettre à jour un Evenement public existant",
 *     security={{"Bearer": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID de l'Evenement public à mettre à jour",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="title", type="string", example="Titre mis à jour du Evenement public"),
 *                 @OA\Property(property="description", type="string", example="Description mise à jour du Evenement public"),
 *                 @OA\Property(property="file", type="string", format="binary", description="Nouveau fichier à télécharger (optionnel)"),
 *                 @OA\Property(
 *                     property="label",
 *                     type="array",
 *                     @OA\Items(type="string"),
 *                     description="Tableau de labels mis à jour"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Evenement public mis à jour avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Mise à jour réussie"),
 *             @OA\Property(
 *                 property="Evenement public",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="title", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(
 *                     property="label",
 *                     type="array",
 *                     @OA\Items(type="string")
 *                 ),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time"),
 *                 @OA\Property(property="file_path", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Evenement public non trouvé",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Evenement public not found")
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

        $annonce = Annonce::findOrFail($id);

        if (!$annonce) {
            return response()->json(["error" => "évènement non trouvé"]);
        }


        try {

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description'=>'required|max:510'
                //'label' => 'required|string|max:255',
                // 'file' => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png,gif|max:10240',
            ]);

            $annonce->title = $validated['title'];
            $annonce->description = $validated['description'] ?? $annonce->description;

            if (isset($validated['label'])) {
                $annonce->label = $validated['label'];
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                if ($annonce->file_path) {
                    Storage::disk('annonce')->delete($annonce->file_path);
                }

                $path = $file->store('', 'annonce');
                File::chmod(storage_path("app/public/AnnonceFile/". $path), 0644);
                $annonce->file_path = $path;
              
            }

            $annonce->save();

            return response()->json(['message' => 'Mise à jour réussie', $annonce]);
        } catch (ValidationException $e) {
           
            return response()->json(['error' => $e->errors()], 422);
        } catch (Exception $e) {
           
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour.'], 500);
        }
    }

/**
     * @OA\Delete(
     *     path="/delete/public/event/{id}",
     *     tags={"Evenement Public"},
     *     summary="Supprimer un Evenement par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du Evenement à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Evenement supprimé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evenement non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function destroy($id)
    {
        $file = Annonce::findOrFail($id);
        $filePath = $file->file_path;
        Storage::disk('annonce')->delete($filePath);
        $file->delete();
        return response()->json(['message' => 'event deleted']);
    }
}
