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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');  // Ajout du patient_id pour relier le rendez-vous au patient
            // $table->string('doctor_name');   // Nom du médecin
            $table->timestamp('start')->nullable();      // Permet de mettre NULL comme valeur par défaut
            $table->timestamp('end')->nullable();        // Permet de mettre NULL comme valeur par défaut
            $table->text('description')->nullable();  // Détails supplémentaires
            $table->timestamps();

            // Définir la contrainte de clé étrangère pour le patient_id
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
