<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\SalleAttente;
use App\Models\User; // Modèle pour les utilisateurs (médecin ou assistant)

class ConsultationController extends Controller
{
    // Récupère toutes les consultations
    public function index()
    {
        $consultations = Consultation::with(['patient', 'utilisateur'])->get();
        return response()->json($consultations);
    }

    // Enregistrer une nouvelle consultation
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'utilisateur_id' => 'required|exists:users,id',
            'date_consultation' => 'required|date',
            'heure_consultation' => 'required|date_format:H:i',
            'motif' => 'required|string',
            'diagnostic' => 'nullable|string',
            'traitement' => 'nullable|string',
        ]);
    
        $consultation = Consultation::create($validatedData);
        SalleAttente::where('patient_id', $validatedData['patient_id'])->update(['sortie' => now()]);

    
        return response()->json([
            'message' => 'Consultation enregistrée avec succès',
            'consultation' => $consultation
        ], 201);
    }
    

    // Affiche les détails d'une consultation
    public function show($id)
    {
        $consultation = Consultation::with(['patient', 'utilisateur'])->find($id);

        if (!$consultation) {
            return response()->json(['message' => 'Consultation introuvable'], 404);
        }

        return response()->json($consultation);
    }

    // Met à jour les informations d'une consultation
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'date_consultation' => 'date',
            'heure_consultation' => 'date_format:H:i',
            'motif' => 'string',
            'diagnostic' => 'nullable|string',
            'traitement' => 'nullable|string',
        ]);

        $consultation = Consultation::findOrFail($id);
        $consultation->update($validatedData);

        return response()->json(['message' => 'Consultation mise à jour avec succès', 'consultation' => $consultation]);
    }

    // Supprime une consultation
    public function destroy($id)
    {
        $consultation = Consultation::findOrFail($id);
        $consultation->delete();

        return response()->json(['message' => 'Consultation supprimée avec succès']);
    }
}
