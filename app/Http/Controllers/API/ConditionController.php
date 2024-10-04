<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Condition;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ConditionController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Condition",
     *     type="object",
     *     title="Condition",
     *     description="Condition Model",
     *     required={"label"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="ID unique de la condition",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="label",
     *         type="string",
     *         description="Nom ou label de la condition",
     *         example="Condition 1"
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Date de création de la condition",
     *         example="2024-10-02T15:11:06Z"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Date de la dernière mise à jour",
     *         example="2024-10-02T15:11:06Z"
     *     )
     * )
     */

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin');
    }
    /**
     * @OA\Get(
     *     path="/conditions-acces",
     *     summary="Get a list of conditions",
     *     tags={"Conditions"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of conditions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="conditions", type="array", @OA\Items(ref="#/components/schemas/Condition"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $conditions = Condition::query()->paginate($perPage);
        return response()->json(['conditions' => $conditions], 200);
    }

    /**
     * @OA\Post(
     *     path="/conditions-acces-store",
     *     summary="Create a new condition",
     *     tags={"Conditions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="label", type="string", example="Condition 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Condition created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="conditions", ref="#/components/schemas/Condition")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation failed")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string'
        ]);
        try {
            $conditions = new Condition([
                'label' => $request->label,
            ]);

            $conditions->save();

            return response()->json(['conditions' => $conditions], 201);
        } catch (Exception $th) {
            return response()->json(['error' => 'erreur lors de la création de la condition ']);
        }
    }

    /**
     * @OA\Put(
     *     path="/conditions-update/{id}",
     *     summary="Update an existing condition",
     *     tags={"Conditions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the condition to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="label", type="string", example="Updated Condition Label")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Condition updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="conditions", ref="#/components/schemas/Condition")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Condition not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Condition not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $conditions = Condition::findOrFail($id);
        if (!$conditions) {
            return response()->json(['error' => 'Condition non trouvé']);
        }

        $request->validate([
            'label' => 'required|string'
        ]);

        try {
            $conditions->label = $request->label;
            $conditions->save();

            return response()->json(['message' => 'mise à jour réussie', 'conditions' => $conditions]);
        } catch (Exception $th) {
            return response()->json(['error' => 'erreur lors de la mise à jour']);
        }
    }

    /**
     * @OA\Delete(
     *     path="/conditions-delete/{id}",
     *     summary="Delete a condition",
     *     tags={"Conditions"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the condition to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Condition deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="suppression réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Condition not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="condition non trouvé")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $conditions = Condition::findOrFail($id);
        if (!$conditions) {
            return response()->json(['error' => 'condition non trouvé']);
        }

        $conditions->delete();
        return response()->json(['success' => 'suppression réussie']);
    }
}
