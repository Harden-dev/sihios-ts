<?php

namespace App\Http\Controllers;

use App\Models\Condition;
use Exception;
use Illuminate\Http\Request;

class ConditionController extends Controller
{
    //
    public function index(Request $request)

    {
        $perPage = $request->input('per_page', 10);

        $conditions = Condition::query()->paginate($perPage);

        return response()->json(['condtions' => $conditions], 200);
    }

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

            return response()->json(['*' => 'mise à jour réussie', 'conditions' => $conditions]);
        } catch (Exception $th) {
            return response()->json(['error' => 'erreur lors de la mise à jour']);
        }
    }


    public function destroy($id)
    {
        $conditions = Condition::findOrfail($id);
        if (!$conditions) {
            return response()->json(['error' => 'condition non trouvé']);
        }

        $conditions->delete();
        return response()->json(['success' => 'suppression réussie']);
    }
}
