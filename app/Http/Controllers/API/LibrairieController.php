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


        $librairies = Librairie::query()->paginate($perPage);
        return response()->json($librairies);
    }

    /**
     * @OA\Post(
     *     path="/librairie",
     *     tags={"Librairies"},
     *     summary="Créer une nouvelle librairie",
     * security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Titre de la librairie"),
     *             @OA\Property(property="auteurs", type="array", @OA\Items(type="integer"), example={1, 2}),
     *             @OA\Property(property="file", type="string", format="binary", description="Fichier à télécharger")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Librairie créée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Librairie")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     )
     * )
     */

    public function store(Request $request)
    {
        Log::info('Début de la méthode store');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Log::info('Informations sur le fichier', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize()
            ]);
            Log::info('Détails de la validation', [
                'file_size' => $request->file('file')->getSize(),
                'max_size' => 10 * 1024 * 1024,
                'is_valid' => $request->file('file')->getSize() <= 10 * 1024 * 1024
            ]);
        } else {
            Log::error('Aucun fichier n\'a été uploadé');
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'auteurs' => 'required|array',
                'auteurs.*:auteurs,id',
                'file' => ['required', 'file', new AllowedFileType, 'max:20480'],
            ]);
            Log::info('Validation réussie');
        } catch (ValidationException $e) {
            Log::error('Erreur de validation', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        }
        try {

            $file = $request->file('file');
            //Log::info('Uploaded file:', $file->toArray());

            $allowedMimeTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($file->getClientMimeType(), $allowedMimeTypes)) {

                // Log::error('Invalid file type:', $file->getClientMimeType());
                return response()->json(['error' => 'Le fichier doit être un PDF ou un document Word.'], 422);
            }

            // Log::info('File mime type:', $file->getClientMimeType());

            $path = $file->store('', 'librairie');

            //  Log::info('File stored at:', $path);

            $librairie = Librairie::create([
                'title' => $request->title,
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $librairie->auteurs()->attach($request->auteurs);
            $auteurs = $librairie->auteurs()->pluck('name')->toArray();
            return response()->json(['librairie' => $librairie, 'auteurs' => $auteurs], 201);
        } catch (Exception $th) {
            //Log::error('Exception:', $th->getMessage());
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }



    public function showFile($id)
    {
        $file = Librairie::findOrFail($id);

        return response()->file(storage_path('app/librairie/' . $file->file_path));
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
     *         description="Détails de la librairie récupérés avec succès",
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
        $auteurs = $librairie->auteurs()->pluck('name')->toArray();
        return response()->json(['librairie' => $librairie, 'auteurs' => $auteurs], 200);
    }


    /**
     * @OA\Put(
     *     path="/librairie/update/{id}",
     *     tags={"Librairies"},
     *     summary="Mettre à jour une librairie existante",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la librairie à mettre à jour",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Titre mis à jour"),
     *             @OA\Property(property="file", type="string", format="binary", description="Fichier à télécharger (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Librairie mise à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Librairie")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Librairie non trouvée",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur du serveur",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $librairie = Librairie::find($id);
        if (!$librairie) {
            return response()->json(['error' => 'Librairie not found'], 404);
        }

        try {

            // Valider les champs du formulaire
            Log::info("Titre : " . $request->title);
            Log::alert($request->all());
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'file' => 'nullable|file|mimes:pdf,docx|max:10240', // 10MB max
            ]);

            // Mettre à jour le titre
            $librairie->title = $request->title;
            Log::info("Titre mis à jour : " . $librairie->title);

            // Vérifier si un fichier est uploadé
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                Log::info("Fichier uploadé : " . $file);
                // Supprimer l'ancien fichier si présent
                if ($librairie->file_path) {
                    Storage::disk('librairie')->delete($librairie->file_path);
                }

                // Stocker le nouveau fichier
                $path = $file->store('', 'librairie');
                $librairie->file_path = $path;

                // Mettre à jour les autres informations du fichier
                $librairie->mime_type = $file->getClientMimeType();
                $librairie->size = $file->getSize();
            }

            // Enregistrer les modifications
            $librairie->save();

            return response()->json(['message' => 'Mise à jour réussie', 'librairie' => $librairie]);
        } catch (Exception $th) {
            Log::error('Erreur lors de la mise à jour : ' . $th->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour.'], 500);
        }
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
        $file = Librairie::findOrFail($id);
        $filePath = $file->file_path;
        Storage::disk('librairie')->delete($filePath);
        $file->delete();
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

        Log::info('File object:', ['file' => $file]);
        Log::info('File path type:', ['type' => gettype($file->file_path)]);
        Log::info('File path content:', ['content' => $file->file_path]);

        if (is_array($file->file_path)) {
            // Si c'est un tableau, prenons le premier élément
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
