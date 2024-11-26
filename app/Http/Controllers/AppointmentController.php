<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return Appointment::with('patient')->get(); // Fetch appointments with patient details
    }

    public function store(Request $request)
    {
        $validated =   $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'description' => 'nullable|string',
        ]);

        $appointment = Appointment::create($validated);
        return response()->json(['message' => 'Appointment created successfully', 'data' => $appointment], 201);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'start' => 'required|date',
            'end' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return $appointment;
    }

    public function destroy($id)
    {
        Appointment::destroy($id);

        return response()->noContent();
    }
}
