<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
   use HasFactory;

    protected $fillable = [
        'section_id',
        'student_id', 
        'date',
        'present',
        'reason'
    ];

    protected $casts = [
        'date' => 'date',
        'present' => 'boolean'
    ];

    /**
     * Relation avec l'étudiant
     */
    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id', 'id_stu');
    }

    /**
     * Relation avec la section
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
