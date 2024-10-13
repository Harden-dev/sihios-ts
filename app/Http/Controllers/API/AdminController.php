<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ApproveMail;
use App\Mail\NewAdminCredentials;
use App\Mail\RejectedMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Log;

class AdminController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="User-Admin",
     *     type="object",
     *     title="Utilisateur Administrateur",
     *     required={"id", "first_name", "last_name", "email", "phone", "job_title", "status"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="L'identifiant unique de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="first_name",
     *         type="string",
     *         description="Prénom de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="last_name",
     *         type="string",
     *         description="Nom de famille de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         description="Adresse e-mail de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="phone",
     *         type="string",
     *         description="Numéro de téléphone de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="job_title",
     *         type="string",
     *         description="Poste de l'utilisateur"
     *     ),
     *     @OA\Property(
     *         property="status",
     *         type="string",
     *         description="Statut de l'utilisateur (ex: approuvé, en attente, rejeté)"
     *     ),
     *     @OA\Property(
     *         property="is_admin",
     *         type="boolean",
     *         description="Indique si l'utilisateur est un administrateur"
     *     )
     * )
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Post(
     *     path="/admin/store",
     *     tags={"Admin"},
     *     summary="Créer un nouvel administrateur",
     * security={{"Bearer": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="job_title", type="string", example="Administrateur"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Administrateur créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object", @OA\Property(property="errors", type="array", @OA\Items(type="string")))
     *     )
     * )
     */

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
        ], $this->messageValidation());

        try {
            $password = Str::random(8);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'job_title' => $request->job_title,
                'password' => Hash::make($password),
                'status' => 'approved',
                'role' => 'admin',
            ]);

            Mail::to($user->email)->send(new NewAdminCredentials($user, $password));

            return response()->json([
                'message' => 'Admin créé avec succès. Un e-mail avec les identifiants a été envoyé.',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'job_title' => $user->job_title,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $th) {

            return response()->json([
                'message' => 'Une erreur s\'est produite.' . $th->getMessage(),
            ], 500);
        }
    }




    /**
     * @OA\Get(
     *     path="/admin/all-member",
     *     tags={"Admin"},
     *     summary="Obtenir tous les membres",
     * security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des membres récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function getAllMember()
    {
        $users = User::withTrashed()->where('role', 'user')->get();
        return response()->json(['users' => $users]);
    }


    /**
     * @OA\Get(
     *     path="/admin/admin-list",
     *     tags={"Admin"},
     *     summary="Obtenir tous les administrateurs",
     * security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des administrateurs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function getAdmin(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $users = User::withTrashed()
            ->where('role', 'admin')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json(['admins' => $users]);
    }


    /**
     * @OA\Get(
     *     path="/admin/active",
     *     tags={"Admin"},
     *     summary="Obtenir tous les membres actifs",
     * security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des membres actifs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function getActiveMember(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $users = User::withTrashed()
            ->where('status', 'approved')
            ->where('role', 'user')
            ->orderByDesc('created_at')
            ->paginate($perPage);
        return response()->json(['users' => $users]);
    }

    /**
     * @OA\Get(
     *     path="/admin/pending-member",
     *     tags={"Admin"},
     *     summary="Obtenir tous les membres en attente",
     * security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des membres en attente récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */

    public function getPendingMember()
    {
        $pendingMember = User::withTrashed()->where('status', 'pending')->where('role', 'user')->get();
        return response()->json(['membre en attente' => $pendingMember]);
    }

    /**
     * @OA\Get(
     *     path="/admin/rejected-member",
     *     tags={"Admin"},
     *     summary="Obtenir tous les membres rejetés",
     * security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des membres rejetés récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */

    public function getRejectMember()
    {
        $rejectMember = User::query()->where('status', 'rejected')->where('role', 'user')->get();
        return response()->json(['membres réjétés' => $rejectMember]);
    }

    /**
     * @OA\Post(
     *     path="/admin/approve-member/{id}",
     *     tags={"Admin"},
     *     summary="Approuver un membre par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du membre à approuver",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre approuvé avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function approveMember($id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['error' => 'Membre non trouvé'], 404);
        }

        $user->status = 'approved';
        $user->save();

        Mail::to($user->email)->send(new ApproveMail($user));
        return response()->json(['message' => 'Membre approuvé avec succès']);
    }

    /**
     * @OA\Post(
     *     path="/admin/reject-member/{id}",
     *     tags={"Admin"},
     *     summary="Rejeter un membre par ID",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du membre à rejeter",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre rejeté avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */
    public function rejectMember($id)
    {
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json(['error' => 'Membre non trouvé'], 404);
        }

        $user->status = 'rejected';
        $user->save();

        Mail::to($user->email)->send(new RejectedMail($user));
        return response()->json(['message' => 'Membre rejeté avec succès']);
    }

    /**
     * @OA\Post(
     *     path="/admin/pending-member/{id}",
     *     tags={"Admin"},
     *     summary="Mettre un membre en attente après approbation",
     * security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du membre à mettre en attente",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Membre mis en attente avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string"))
     *     )
     * )
     */

    public function pendingMemberAfterApprove($id)
    {
        $user = User::findOrFail($id);

        if ($user->status  = 'approved') {

            $user->status = 'pending';
            $user->save();
            return response()->json(['message' => 'Membre désactivé avec succès']);
        }
        return response()->json(['message' => "Désolé, membre ne peut pas être désactivé. Veuillez si c'est un memebre déja approuvé"]);
    }

    /**
     * @OA\Put(
     *     path="/admin/change-status/{id}/member",
     *     tags={"Admin"},
     *     summary="Changer le statut d'un membre (activer/désactiver)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du membre à activer ou désactiver",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut du membre changé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Activation réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Membre non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Membre non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la modification du statut",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Une erreur est survenue")
     *         )
     *     )
     * )
     */

    public function changeMemberStatus($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->restore();
            return response()->json(['message' => "Activation réussie"]);
        } else {
            $user->delete();
            return response()->json(['message' => "Désactivation réussie"]);
        }
    }

    protected function messageValidation()
    {
        return [

            'first_name.required' => 'Le prénom est obligatoire.',
            'first_name.string' => 'Le prénom doit être une chaîne de caractères.',
            'first_name.max' => 'Le prénom ne doit pas dépasser 255 caractères.',

            'last_name.required' => 'Le nom de famille est obligatoire.',
            'last_name.string' => 'Le nom de famille doit être une chaîne de caractères.',
            'last_name.max' => 'Le nom de famille ne doit pas dépasser 255 caractères.',

            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.string' => 'L\'adresse e-mail doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse e-mail doit être une adresse e-mail valide.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 255 caractères.',

            'job_title.required' => 'Le titre du poste est obligatoire.',
            'job_title.string' => 'Le titre du poste doit être une chaîne de caractères.',
            'job_title.max' => 'Le titre du poste ne doit pas dépasser 255 caractères.',

            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',

        ];
    }
}
