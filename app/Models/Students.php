<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    protected $table = "students";
    protected $primaryKey = "id_stu";

    protected $fillable = [
        "nom",
        "prenom",
        "numero",
        "genre",
        "date_naissance",
        "niveau_sco",
        "status",
        "gmail",
        "filiere",
        "cin",
        "adresse"
    ];

    // ✅ علاقة مع جدول النتائج (بدون إظهار النقاط)
    public function resultat()
    {
        return $this->hasOne(Resultat::class, "id_stu", "id_stu");
    }

    // ✅ علاقة مع Section
    public function sectionStudent()
    {
        return $this->hasOne(Section::class, "student_id", "id_stu");
    }

    // ✅ علاقة مع Document
    public function documents()
    {
        return $this->hasMany(Document::class, "student_id", "id_stu");
    }

    // ✅ علاقة مع Attendance
    public function attendance()
    {
        return $this->hasMany(Attendance::class, "student_id", "id_stu");
    }
}
