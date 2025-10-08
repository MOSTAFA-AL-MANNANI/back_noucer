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
        Schema::create('students', function (Blueprint $table) {
            $table->id('id_stu');
            $table->string('nom');
            $table->string('prenom');
            $table->string('cin');
            $table->string('genre');
            $table->string('gmail');
            $table->string('numero');
            $table->string('adresse');
            $table->string('niveau_sco');
            $table->string('filiere');
            $table->date('date_naissance');
            $table->enum('status',['registred','passed','attende','in_interview']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students_tavle');
    }
};
