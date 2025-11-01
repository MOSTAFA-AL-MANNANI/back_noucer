<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personel;
use App\Models\Resultat;
use App\Models\Skills;
use App\Models\Students;
use App\Models\Technique;
use App\Models\User;

class Entretien extends Controller
{
    // ===================== Personel =====================
    public function getPersonels()
    {
        return response()->json(Personel::all(), 200);
    }

    public function ajouterPer(Request $request)
    {
        $per = Personel::create($request->all());
        return response()->json(["message" => "Le personnel est bien ajouté", "data" => $per], 201);
    }

    public function modifierPer(Request $request, $id)
    {
        $per = Personel::findOrFail($id);
        $per->update($request->all());
        return response()->json(["message" => "Le personnel est bien modifié", "data" => $per], 200);
    }

    public function supprimerPer($id)
    {
        $deleted = Personel::destroy($id);
        return $deleted
            ? response()->json(["message" => "Le personnel id=$id est bien supprimé"], 200)
            : response()->json(["message" => "Erreur : le personnel id=$id n'est pas supprimé"], 400);
    }

    // ===================== Technique =====================
    public function getTechniques()
    {
        return response()->json(Technique::all(), 200);
    }

    public function ajouterTech(Request $request)
    {
        $tech = Technique::create($request->all());
        return response()->json(["message" => "La technique est bien ajoutée", "data" => $tech], 201);
    }

    public function modifierTech(Request $request, $id)
    {
        $tech = Technique::findOrFail($id);
        $tech->update($request->all());
        return response()->json(["message" => "La technique est bien modifiée", "data" => $tech], 200);
    }

    public function supprimerTech($id)
    {
        $deleted = Technique::destroy($id);
        return $deleted
            ? response()->json(["message" => "La technique id=$id est bien supprimée"], 200)
            : response()->json(["message" => "Erreur : la technique id=$id n'est pas supprimée"], 400);
    }

    // ===================== Students =====================
    public function getStudents()
    {
        return response()->json(Students::all(), 200);
    }

    public function ajouterStu(Request $request)
    {
        $stu = Students::create($request->all());
        return response()->json(["message" => "L'étudiant est bien ajouté", "data" => $stu], 201);
    }

    public function modifierStu(Request $request, $id)
    {
        $student = \App\Models\Students::where('id_stu', $id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

       $student->update($request->only([
            'nom', 'prenom', 'numero', 'genre', 'date_naissance',
            'niveau_sco', 'status', 'gmail', 'filiere', 'cin', 'adresse'
        ]));

        return response()->json(['message' => 'Student updated successfully', 'student' => $student]);
    }

    public function supprimerStu($id)
    {
        $deleted = Students::destroy($id);
        return $deleted
            ? response()->json(["message" => "L'étudiant id=$id est bien supprimé"], 200)
            : response()->json(["message" => "Erreur : l'étudiant id=$id n'est pas supprimé"], 400);
    }

    // ===================== Resultats =====================
    public function getResultats()
    {
        return response()->json(Resultat::all(), 200);
    }

public function ajouterResu(Request $request)
{
    // ✅ التحقق من صحة البيانات
    $validated = $request->validate([
        'id_stu'  => 'required|exists:students,id_stu',
        'scoreP'  => 'required|numeric|min:0',
        'scoreT'  => 'required|numeric|min:0',
        'scoreS'  => 'nullable|numeric|min:0',
        'total'   => 'required|numeric|min:0',
    ]);

    // ✅ إنشاء résultat
    $res = Resultat::create($validated);

    // ✅ تحديث حالة الطالب
    Students::where('id_stu', $validated['id_stu'])
        ->update(['status' => 'in_interview']);

    return response()->json([
        "message" => "✅ Le résultat est bien ajouté",
        "data"    => $res
    ], 201);
}



    // ✅ جلب التلاميذ في حالة انتظار مع ترتيبهم حسب النقاط
    // public function getWaitingStudents()
    // {
    //     $waiting = Students::with('Resultat')
    //         ->where('status', 'attende')
    //         ->orderByDesc(
    //             Resultat::select('total')
    //                 ->whereColumn('resultat.id_stu', 'students.id_stu')
    //                 ->limit(1)
    //         )
    //         ->get();

    //     return response()->json($waiting, 200);
    // }



// ✅ جلب تفاصيل طالب واحد مع نتيجتو
public function getStudentDetail($id)
{
    $student = Students::with('resultat')
        ->where('id_stu', $id)
        ->firstOrFail();

    return response()->json($student, 200);
}

    public function index(Request $request)
    {
        $filiere = $request->query('filiere');

        if ($filiere) {
            $techniques = Technique::where('filiere', $filiere)->get();
        } else {
            $techniques = Technique::all();
        }

        return response()->json($techniques);
    }
    public function show($id)
    {
        $student = Students::where('id_stu', $id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json($student);
    }
public function topStudentsByFiliere($filiere)
{
    // Récupérer les 30 meilleurs étudiants de la filière
    $students = \App\Models\Students::with('resultat')
        ->where('filiere', $filiere)
        ->where('status', 'in_interview')
        ->join('resultat', 'students.id_stu', '=', 'resultat.id_stu')
        ->orderByDesc('resultat.total')
        ->select('students.*', 'resultat.scoreP', 'resultat.scoreT', 'resultat.scoreS', 'resultat.total')
        ->take(30)
        ->get();

    // Extraire les ID des meilleurs étudiants
    $topStudentIds = $students->pluck('id_stu')->toArray();

    // Mettre à jour le statut des autres étudiants de la même filière
    \App\Models\Students::where('filiere', $filiere)
        ->whereNotIn('id_stu', $topStudentIds)
        ->update(['status' => 'attende']);

    // Retourner la liste des 30 meilleurs
    return response()->json($students);
}
// جلب الطلاب في الانتظار حسب الفِرقة
public function getWaitingStudentsByFiliere()
{
    // 🟩 récupérer toutes les filières des étudiants en attente
    $filieres = Students::where('status', 'attende')
        ->distinct()
        ->pluck('filiere');

    $result = [];

    foreach ($filieres as $filiere) {
        // 🟦 récupérer les étudiants + leurs notes (via jointure)
        $students = Students::with('resultat')
            ->where('status', 'attende')
            ->where('filiere', $filiere)
            ->leftJoin('resultat', 'students.id_stu', '=', 'resultat.id_stu')
            ->orderByDesc('resultat.total')
            ->select(
                'students.id_stu',
                'students.nom',
                'students.prenom',
                'students.numero',
                'students.genre',
                'students.date_naissance',
                'students.niveau_sco',
                'students.status',
                'students.gmail',
                'students.filiere',
                'students.cin',
                'students.adresse',
                'resultat.scoreP',
                'resultat.scoreT',
                'resultat.scoreS',
                'resultat.total'
            )
            ->get();

        $result[$filiere] = $students;
    }

    return response()->json($result, 200);
}


// تحديث حالة الطالب
public function updateStatus(Request $request, $id)
{
    $student = Students::findOrFail($id);
    $student->status = $request->status;
    $student->save();

    return response()->json([
        'message' => 'Status mis à jour avec succès',
        'student' => $student
    ], 200);
}

// جلب قائمة الفِرَق
public function getFilieres()
{
    $filieres = Students::distinct()->pluck('filiere');
    return response()->json($filieres, 200);
}
}


