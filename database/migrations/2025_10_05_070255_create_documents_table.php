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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
                        $table->unsignedBigInteger('student_id'); 
            $table->foreign('student_id')
                  ->references('id_stu')  // ← لأن المفتاح الأساسي في students هو id_stu
                  ->on('students')
                  ->onDelete('cascade');
            $table->boolean('photo')->default(false);
            $table->boolean('cin')->default(false);
            $table->boolean('cv')->default(false);
            $table->boolean('bac')->default(false);
            $table->boolean('convocation')->default(false);
            $table->boolean('radiographies_thoraciques')->default(false);
            $table->boolean('bonne_conduite')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
