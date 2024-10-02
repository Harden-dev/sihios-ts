<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Rules\AllowedFileType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Documentation API SIHIOS-TS",
 *     version="1.0.0",
 *     description="API de la plateforme SIHIOS-TS",
 *     @OA\Contact(
 *         name="Developer",
 *         email="michel.banh@softskills.ci"
 *     )
 * )
 * 
 * @OA\Tag(
 *     name="Event",
 *     description="Gestion des événements."
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur local"
 * )
 */
class EventController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Event",
     *     type="object",
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="title", type="string"),
     *     @OA\Property(property="label", type="string"),
     *     @OA\Property(property="file_path", type="string"),
     *     @OA\Property(property="mime_type", type="string"),
     *     @OA\Property(property="size", type="integer")
     * )
     */

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/events",
     *     summary="Lister les événements",
     * security={{"Bearer": {}}},
     *     tags={"Event"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste paginée des événements",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Event")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $events = Event::query()->paginate($perPage);
        return response()->json($events);
    }

    /**
     * @OA\Post(
     *     path="/events",
     *     summary="Créer un nouvel événement",
     * security={{"Bearer": {}}},
     *     tags={"Event"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Conférence annuelle"),
     *             @OA\Property(property="label", type="string", example="Important"),
     *             @OA\Property(property="file", type="string", format="binary", description="Fichier à uploader")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Événement créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
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
    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'label' => 'required',
                'file' => ['required', 'file', new AllowedFileType, 'max:20480'],
            ]);
            Log::info('Validation réussie');
        } catch (ValidationException $e) {
            Log::error('Erreur de validation', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        }
        try {

            $file = $request->file('file');


            $allowedMimeTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            if (!in_array($file->getClientMimeType(), $allowedMimeTypes)) {


                return response()->json(['error' => 'Le fichier doit être un PDF, un document Word ou une image'], 422);
            }

            $path = $file->store('', 'event');
            $events = Event::create([
                'title' => $request->title,
                'label' => $request->label,
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $events->file_url = asset('storage/eventFile/' . $path);
     


            return response()->json($events, 201);
        } catch (Exception $th) {

            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/event/detail/{id}",
     *     tags={"Event"},
     *     summary="Obtenir les détails d'un événement par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'événement à récupérer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'événement récupérés avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Événement non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function showEventDetailById($id)
    {
        $event = Event::findOrFail($id);
        return response()->json(['event' => $event], 200);
    }
    /**
     * @OA\Put(
     *     path="/event/update/{id}",
     *     tags={"Event"},
     *     summary="Update an existing event",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the event to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Event Title"),
     *             @OA\Property(property="label", type="string", example="Updated Label"),
     *             @OA\Property(property="file", type="string", format="binary", description="File to upload (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        try {
            // Valider les champs du formulaire
            $request->validate([
                'title' => 'required|string|max:255',
                'label' => 'required|string|max:255',
                'file' => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png,gif|max:10240',
            ]);

            $event->title = $request->title;
            $event->label = $request->label;

            // Vérifier si un fichier est uploadé
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                // Supprimer l'ancien fichier si présent
                if ($event->file_path) {
                    Storage::disk('event')->delete($event->file_path);
                }

                // Stocker le nouveau fichier
                $path = $file->store('', 'event');
                $event->file_path = $path;
                $event->mime_type = $file->getClientMimeType();
                $event->size = $file->getSize();
            }

            // Enregistrer les modifications
            $event->save();

            return response()->json(['message' => 'Mise à jour réussie', 'event' => $event]);
        } catch (Exception $th) {
            Log::error('Erreur lors de la mise à jour : ' . $th->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour.' . $th->getMessage()], 500);
        }
    }

/**
 * @OA\Delete(
 *     path="/event/delete/{id}",
 *     tags={"Event"},
 *     summary="Supprimer un événement par ID",
 * security={{"Bearer": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de l'événement à supprimer",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Événement supprimé avec succès",
 *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Événement non trouvé",
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
        $file = Event::findOrFail($id);
        $filePath = $file->file_path;
        Storage::disk('event')->delete($filePath);
        $file->delete();
        return response()->json(['message' => 'event deleted']);
    }
}