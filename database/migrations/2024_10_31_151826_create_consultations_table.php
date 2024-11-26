<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id'); // Clé étrangère pour relier au patient
            $table->unsignedBigInteger('utilisateur_id'); // Clé étrangère pour relier au médecin ou assistant
            $table->date('date_consultation'); // Date de la consultation
            $table->time('heure_consultation'); // Heure de la consultation
            $table->text('motif'); // Motif de la consultation
            $table->text('diagnostic')->nullable(); // Diagnostic
            $table->text('traitement')->nullable(); // Traitement prescrit
            $table->timestamps();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('utilisateur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
