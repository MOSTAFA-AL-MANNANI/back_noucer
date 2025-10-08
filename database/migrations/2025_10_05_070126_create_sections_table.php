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
        Schema::create('sections', function (Blueprint $table) {
            $table->id(); // رقم القسم
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete(); // ربط الشعبة
            $table->string('name'); // اسم القسم
            $table->integer('capacity')->default(30); // عدد المقاعد
            $table->date('start_date'); // تاريخ البداية
            $table->date('end_date')->nullable(); // تاريخ النهاية
            $table->enum('status', ['scheduled', 'active', 'finished'])->default('scheduled'); // حالة القسم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
