<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'filiere_id', 'name', 'capacity', 'start_date', 'end_date', 'status'
    ];

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function students()
    {
        return $this->belongsToMany(Students::class, 'section_student')
                    ->withPivot('enrolled_at', 'status')
                    ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
