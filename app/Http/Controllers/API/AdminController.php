<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function StoreAdmin(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role'=> 'required'

        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
            'password' => Hash::make($request->password),
            'status' => 'approved',
            'role' => 'admin',
        ]);

        return response()->json(['message' => 'Admin created successfully', 'user' => $user], 201);
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
    public function getAdmin()
    {
        $users = User::withTrashed()->where('role', 'admin')->get();
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
    public function getActiveMember()
    {
        $users = User::withTrashed()->where('status', 'approved')->where('role','user')->get();
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
        $rejectMember = User::query()->where('status', 'rejected')->where('role','user')->get();
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


        $user->status = 'approved';
        $user->save();
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
        $user->status = 'rejected';
        $user->save();
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
}
