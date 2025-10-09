<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionStudent extends Model
{
    use HasFactory;

    protected $table = 'section_student';

    protected $fillable = [
        'section_id', 'student_id', 'enrolled_at', 'status'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function student()
    {
        return $this->belongsTo(Students::class, 'student_id', 'id_stu');
    }
}
