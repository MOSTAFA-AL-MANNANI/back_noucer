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
        Schema::create('section_student', function (Blueprint $table) {
            $table->id(); // رقم التسجيل
            $table->foreignId('section_id')->constrained()->cascadeOnDelete(); // رقم القسم
                       $table->unsignedBigInteger('student_id'); 
            $table->foreign('student_id')
                  ->references('id_stu')  // ← لأن المفتاح الأساسي في students هو id_stu
                  ->on('students')
                  ->onDelete('cascade');
            $table->date('enrolled_at')->default(now()); // تاريخ التسجيل
            $table->enum('status', ['active', 'left', 'expelled'])->default('active'); // حالة التلميذ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_student');
    }
};
