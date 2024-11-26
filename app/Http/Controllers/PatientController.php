<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\SalleAttente;

class PatientController extends Controller
{
    //
    public function index()
    {
        return response()->json(Patient::all());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom_de_famille' => 'required|string|max:255',
            'sexe' => 'required|in:Homme,Femme',
            'groupe_sanguin' => 'nullable|string|max:3',
            'adresse' => 'nullable|string|max:255',
            'commune' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'telephone' => 'nullable|string|max:15',
            'contact_urgence' => 'nullable|string|max:15',
            'antecedents_medicaux' => 'nullable|string',
            'antecedents_chirurgicaux' => 'nullable|string',
            'antecedents_familiales' => 'nullable|string',
            'allergies_connues' => 'nullable|string',
        ]);

        // Enregistrer les données validées dans la base de données
        $patient = Patient::create($validatedData);

        return response()->json(['message' => 'Patient enregistré avec succès', 'patient' => $patient], 201);
    }
    public function mettreEnSalleDAttente($id)
    {
        $patient = Patient::findOrFail($id);

        // Vérifie si le patient est déjà en salle d'attente aujourd'hui
        $dejaEnSalleDAttente = SalleAttente::where('patient_id', $patient->id)
            ->whereDate('entree', now()->toDateString())
            ->whereNull('sortie') // Vérifie qu'il n'est pas encore sorti
            ->exists();

        if ($dejaEnSalleDAttente) {
            return response()->json(['message' => 'Le patient est déjà en salle d\'attente aujourd\'hui'], 400);
        }

        // Ajoute le patient en salle d'attente si aucune entrée n'existe pour aujourd'hui
        SalleAttente::create([
            'patient_id' => $patient->id,
            'entree' => now(),
        ]);

        return response()->json(['message' => 'Patient ajouté à la salle d\'attente']);
    }

    public function retirerDeSalleDAttente($id)
    {
        $attente = SalleAttente::where('patient_id', $id)->whereNull('sortie')->first();
        if ($attente) {
            $attente->sortie = now();
            $attente->save();
        }

        return response()->json(['message' => 'Patient retiré de la salle d\'attente']);
    }
    public function patientsEnSalleAttente()
    {
        // Filtre les patients en salle d'attente aujourd'hui sans heure de sortie
        $patientsEnAttente = SalleAttente::whereNull('sortie')
            ->whereDate('entree', now()->toDateString()) // Vérifie que l'entrée est d'aujourd'hui
            ->with('patient') // Récupère les détails du patient
            ->get()
            ->map(function ($attente) {
                return $attente->patient;
            });

        return response()->json($patientsEnAttente);
    }
    public function historiqueSalleAttente()
    {
        // Récupère tous les patients en salle d'attente sans heure de sortie
        $historiqueSalleAttente = SalleAttente::with('patient') // Récupère les détails du patient
            ->get()
            ->map(function ($attente) {
                return $attente->patient;
            });

        return response()->json($historiqueSalleAttente);
    }
    public function show($id)
    {
        // Find the patient by ID
        $patient = Patient::find($id);

        // Check if the patient exists
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        // Return the patient details as JSON
        return response()->json($patient);
    }
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validatedData = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom_de_famille' => 'required|string|max:255',
            'sexe' => 'required|in:Homme,Femme',
            'groupe_sanguin' => 'nullable|string|max:3',
            'adresse' => 'nullable|string|max:255',
            'commune' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'telephone' => 'nullable|string|max:15',
            'contact_urgence' => 'nullable|string|max:15',
            'antecedents_medicaux' => 'nullable|string',
            'antecedents_chirurgicaux' => 'nullable|string',
            'antecedents_familiales' => 'nullable|string',
            'allergies_connues' => 'nullable|string',
        ]);

        $patient->update($validatedData);

        return response()->json(['message' => 'Patient modifié avec succès', 'patient' => $patient]);
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return response()->json(['message' => 'Patient supprimé avec succès']);
    }
}
