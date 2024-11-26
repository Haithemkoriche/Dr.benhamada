<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});
Route::post('/patients', [PatientController::class, 'store']);
Route::get('/patients/liste', [PatientController::class, 'index']);
Route::post('/patients/{id}/salle-attente', [PatientController::class, 'mettreEnSalleDAttente']);
Route::get('/patients/salle-attente', [PatientController::class, 'patientsEnSalleAttente']);
Route::get('/patients/histoique-salle-attente', [PatientController::class, 'historiqueSalleAttente']);
Route::get('/patients/{id}', [PatientController::class, 'show']);
Route::delete('/patients/{id}', [PatientController::class, 'destroy']);
Route::put('/patients/{id}', [PatientController::class, 'update']);
Route::post('/consultations', [ConsultationController::class, 'store']);
Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store']);
Route::put('/payments/{id}', [PaymentController::class, 'update']);
Route::put('/payments/{id}/mark-paid', [PaymentController::class, 'markPaid']);
Route::get('/consultations', [ConsultationController::class, 'index']);
Route::get('/consultations/{id}', [ConsultationController::class, 'show']);
Route::put('/consultations/{id}', [ConsultationController::class, 'update']);

Route::get('/appointments', [AppointmentController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'role:medecin'])->group(function () {
    Route::post('/permissions', [PermissionController::class, 'store']);
});
