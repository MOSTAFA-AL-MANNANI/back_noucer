<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\Section;
use App\Models\Students;
use App\Models\SectionStudent;
use App\Models\Attendance;
use App\Models\Document;
use App\Models\Resultat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class Student extends Controller
{

        public function getStudents($filiere)
    {
        $students = Students::where('status', 'passed')
            ->where('filiere', $filiere)
            ->get();

        return response()->json($students);
    }
    public function getDocuments($id)
{
    $student = Students::findOrFail($id);
    $document = Document::where('student_id', $id)->first();

    $allDocs = [
        'photo', 'cin', 'cv', 'bac',
        'convocation', 'radiographies_thoraciques', 'bonne_conduite'
    ];

    $response = [];
    foreach ($allDocs as $doc) {
        // إذا القيمة موجودة و true أو active → checkbox = true
        $response[$doc] = $document && ($document->$doc == true ) ? true : false;
    }

    return response()->json($response);
}
public function updateDocument(Request $request, $id)
{
    $field = $request->field;   // اسم المستند
    $value = $request->value;   // true أو false

    $document = Document::firstOrCreate(['student_id' => $id]);

    // حفظ القيمة مباشرة (true/false أو active/null حسب التصميم)
    $document->$field = $value ? true : false;
    $document->save();

    
    return response()->json(['success' => true, 'new_value' => $document->$field]);
}

public function passTopStudents($filiere)
{
    try {
        // جلب أفضل 30 طالب حسب النقاط
        $students = \App\Models\Students::with('resultat')
            ->where('filiere', $filiere)
            ->where('status', 'in_interview')
            ->join('resultat', 'students.id_stu', '=', 'resultat.id_stu')
            ->orderByDesc('resultat.total')
            ->select('students.*')
            ->take(30)
            ->get();

        // تحديث الحالة لجميع الطلاب
        foreach ($students as $student) {
            $student->status = 'passed';
            $student->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Le statut des meilleurs 30 étudiants a été mis à jour en 'passed'.",
            'students_updated' => $students->pluck('id_stu')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la mise à jour des statuts : " . $e->getMessage()
        ], 500);
    }
}

    // جلب جميع الطلاب في قسم معين
    public function getStudentsBySection($section_id)
    {
        $section = Section::with(['students'])->find($section_id);

        if (!$section) {
            return response()->json(['message' => 'Section non trouvée'], 404);
        }

        return response()->json([
            'section' => $section,
            'students' => $section->students
        ]);
    }

        public function getAbsencesByStudent($id)
    {
        $absences = Attendance::where('student_id', $id)
            ->where('present', false)
            ->with('section')
            ->get();
        return $absences;
    }

}
