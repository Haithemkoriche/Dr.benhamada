<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function store(Request $request)
    {
        // Valider les données de la requête
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        // Créer et enregistrer la permission
        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Permission enregistrée avec succès.',
            'permission' => $permission
        ], 201);
    }
}
