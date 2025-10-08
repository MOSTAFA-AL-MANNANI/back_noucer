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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // رقم الحضور
            $table->foreignId('section_id')->constrained()->cascadeOnDelete(); // القسم
                        $table->unsignedBigInteger('student_id'); 
            $table->foreign('student_id')
                  ->references('id_stu')  // ← لأن المفتاح الأساسي في students هو id_stu
                  ->on('students')
                  ->onDelete('cascade');
            $table->date('date'); // تاريخ الحصة
            $table->boolean('present')->default(1); // 1=حاضر، 0=غائب
            $table->string('reason')->nullable(); // سبب الغياب إن وجد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
