<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Consultation;
use App\Models\Patient;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    //
    public function index()
    {
        // Ensure that we are eager loading the 'consultation.patient' relationships
        $payments = Payment::with(['consultation.patient'])->get();
    
        // Log the response to debug its structure
        return response()->json($payments);
    }
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'patient_id' => 'required|exists:patients,id', // Ajout de patient_id
            'montant' => 'required|numeric',
            'versement' => 'required|numeric',
            'reste' => 'required|numeric',
            'motif' => 'required|string',
            'date' => 'required|date',
        ]);
    
        $payment = Payment::create($validatedData);
    
        return response()->json($payment, 201);
    }
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validatedData = $request->validate([
            'montant' => 'required|numeric',
            'versement' => 'required|numeric',
            'reste' => 'required|numeric',
            'motif' => 'required|string',
            'date' => 'required|date',
        ]);

        $payment->update($validatedData);

        return response()->json($payment);
    }

    // Mark the payment as paid (set reste to 0)
    public function markPaid($id)
    {
        $payment = Payment::findOrFail($id);
    
        // Assuming 'montant' is a column on the Payment model
        $montant = $payment->montant; // Get the montant value from the payment
    
        // Mark the payment as fully paid by setting 'versement' to 'montant'
        $payment->versement = $montant;
        $payment->reste = 0; // If you want to set 'reste' to 0 to indicate payment is completed
        $payment->save();
    
        return response()->json($payment);
    }
}
