<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
        protected $table = "students";
    protected $primaryKey = "id_stu";
    protected $fillable = ["nom","prenom","numero","genre","date_naissance","niveau_sco","status",
                            "gmail","filiere","cin","adresse"];
        public function Resultat(){
        return $this->hasOne(Resultat::class, "id_stu", "id_stu");
    }
}
