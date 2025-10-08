<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technique extends Model
{
    protected $table = "technique";
    protected $primaryKey = "id";
    protected $fillable = ["question","filiere"];
}
