<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'photo',
        'cin',
        'cv',
        'bac',
        'convocation',
        'radiographies_thoraciques',
        'bonne_conduite'
    ];

    public function student()
    {
        return $this->belongsTo(Students::class, "student_id", "id_stu");
    }
}
