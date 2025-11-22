<?php

namespace App\Http\Controllers;

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

class GestionScolaireController extends Controller
{
public function getStudents()
{
    $students = Students::paginate(30);
    return response()->json([
        'data' => $students->items(), // Les étudiants de la page actuelle
        'current_page' => $students->currentPage(),
        'last_page' => $students->lastPage(),
        'per_page' => $students->perPage(),
        'total' => $students->total(),
        'from' => $students->firstItem(),
        'to' => $students->lastItem(),
    ], 200);
}
        public function indexF()
    {
        $filieres = Filiere::all();
        return response()->json($filieres);
    }
    // 🏫 إضافة شعبة
    public function ajouterFiliere(Request $request)
    {
        $filiere = Filiere::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['message' => 'تمت إضافة الشعبة بنجاح', 'data' => $filiere]);
    }

        public function updateF(Request $request, $id)
    {
        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'الشعبة غير موجودة'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $filiere->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'تم تعديل الشعبة بنجاح', 'data' => $filiere]);
    }

    // حذف شعبة
    public function destroyF($id)
    {
        $filiere = Filiere::find($id);
        if (!$filiere) {
            return response()->json(['message' => 'الشعبة غير موجودة'], 404);
        }

        $filiere->delete();
        return response()->json(['message' => 'تم حذف الشعبة بنجاح']);
    }
    // 🏫 إنشاء قسم جديد
    // Afficher toutes les sections
    public function indexS()
    {
        $sections = Section::with('filiere')->get(); // Inclure la filière liée
        return response()->json($sections);
    }
    // Ajouter une nouvelle section
    public function storeS(Request $request)
    {
        $request->validate([
            'filiere_id' => 'required|exists:filieres,id',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:scheduled,active,finished',
        ]);

        $section = Section::create([
            'filiere_id' => $request->filiere_id,
            'name' => $request->name,
            'capacity' => $request->capacity ?? 30,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status ?? 'scheduled',
        ]);

        return response()->json(['message' => 'Section ajoutée avec succès', 'data' => $section]);
    }

    // Modifier une section
    public function updateS(Request $request, $id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section non trouvée'], 404);
        }

        $request->validate([
            'filiere_id' => 'required|exists:filieres,id',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:scheduled,active,finished',
        ]);

        $section->update([
            'filiere_id' => $request->filiere_id,
            'name' => $request->name,
            'capacity' => $request->capacity ?? 30,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status ?? 'scheduled',
        ]);

        return response()->json(['message' => 'Section mise à jour avec succès', 'data' => $section]);
    }

    // Supprimer une section
    public function destroyS($id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json(['message' => 'Section non trouvée'], 404);
        }

        $section->delete();
        return response()->json(['message' => 'Section supprimée avec succès']);
    }

    // ✅ 2. جلب الأقسام حسب اسم الشعبة
    public function getSectionsByFiliere($filiereName)
    {
        return Section::whereHas('filiere', function ($q) use ($filiereName) {
            $q->where('name', $filiereName);
        })->get();
    }

    // ✅ 3. جلب أفضل 30 تلميذ في شعبة معينة
     public function getTopStudentsByFiliere(Request $request)
    {
        $filiere = $request->input('filiere');
        
        $students = DB::table('students')
            ->join('resultat', 'resultat.id_stu', '=', 'students.id_stu')
            ->where('students.filiere', $filiere)
            ->whereNotIn('students.id_stu', function($query) {
                $query->select('student_id')->from('section_student');
            })
            ->select('students.*', 'resultat.total')
            ->orderByDesc('resultat.total')
            ->limit(30)
            ->get();

        return response()->json($students);
    }

    // ✅ 4. تسجيل التلاميذ المختارين في القسم
    public function enrollStudents(Request $request)
    {
        $section_id = $request->input('section_id');
        $student_ids = $request->input('student_ids');

        foreach ($student_ids as $id) {
            SectionStudent::create([
                'section_id' => $section_id,
                'student_id' => $id,
                'enrolled_at' => now(),
                'status' => 'active',
            ]);
        }
        return response()->json(['message' => 'تم تسجيل التلاميذ بنجاح']);
    }

    // ✅ 5. عرض التلاميذ المسجلين في قسم معين
   // جلب التلاميذ الموجودين في قسم معين

    public function getStudentsInSection($section_id)
{
    $students = DB::table('section_student')
        ->join('students', 'section_student.student_id', '=', 'students.id_stu')
        ->join('sections', 'section_student.section_id', '=', 'sections.id') // اختياري إذا أردت معلومات عن القسم
        ->where('section_student.section_id', $section_id)
        ->select(
            'students.id_stu',
            'students.nom',
            'students.prenom',
            'students.cin',
            'students.numero',
            'students.gmail',
            'students.genre',
            'students.niveau_sco',
            'students.date_naissance',
            'students.adresse',
            'students.filiere',
            'students.status',
            'section_student.id as section_student_id',
            'section_student.created_at as date_affectation',
            'sections.name' // فقط إذا يوجد جدول sections
        )
        ->get();

    return response()->json($students);
}



    public function markAttendance(Request $request)
    {
        // Valider les données
        $request->validate([
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|exists:students,id_stu',
            'attendances.*.section_id' => 'required|exists:sections,id',
            'attendances.*.date' => 'required|date',
            'attendances.*.present' => 'required|boolean',
            'attendances.*.reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            $createdAttendances = [];
            
            foreach ($request->attendances as $attendanceData) {
                // Vérifier si l'étudiant existe
                $student = Students::where('id_stu', $attendanceData['student_id'])->first();
                if (!$student) {
                    throw new \Exception("Student not found: " . $attendanceData['student_id']);
                }

                // Vérifier si une présence existe déjà pour cette date et cet étudiant
                $existingAttendance = Attendance::where('student_id', $attendanceData['student_id'])
                    ->where('section_id', $attendanceData['section_id'])
                    ->whereDate('date', $attendanceData['date'])
                    ->first();

                if ($existingAttendance) {
                    // Mettre à jour l'enregistrement existant
                    $existingAttendance->update([
                        'present' => $attendanceData['present'],
                        'reason' => $attendanceData['present'] ? null : ($attendanceData['reason'] ?? null),
                    ]);
                    $createdAttendances[] = $existingAttendance;
                } else {
                    // Créer un nouvel enregistrement
                    $attendance = Attendance::create([
                        'student_id' => $attendanceData['student_id'],
                        'section_id' => $attendanceData['section_id'],
                        'date' => $attendanceData['date'],
                        'present' => $attendanceData['present'],
                        'reason' => $attendanceData['present'] ? null : ($attendanceData['reason'] ?? null),
                    ]);
                    $createdAttendances[] = $attendance;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'data' => $createdAttendances,
                'count' => count($createdAttendances)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error recording attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les présences d'une section pour une date
     */
        public function getSectionAttendance($sectionId, $date)
        {
            try {
                $attendances = Attendance::with('student') // ← Correction: 'student' au lieu de 'students'
                    ->where('section_id', $sectionId)
                    ->whereDate('date', $date)
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => $attendances
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching attendance data'
                ], 500);
            }
        }


    public function ajouterDocuments(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id_stu',
            'photo' => 'boolean',
            'cin' => 'boolean',
            'cv' => 'boolean',
            'bac' => 'boolean',
            'convocation' => 'boolean',
            'radiographies_thoraciques' => 'boolean',
            'bonne_conduite' => 'boolean',
        ]);

        $document = Document::updateOrCreate(
            ['student_id' => $validated['student_id']],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => '📎 Documents enregistrés avec succès',
            'data' => $document
        ]);
    }

    // 🔍 2. Vérifier les documents manquants
    public function checkMissingDocuments($student_id)
    {
        $document = Document::where('student_id', $student_id)->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun document trouvé pour cet étudiant'
            ]);
        }

        $missing = [];
        foreach (['photo', 'cin', 'cv', 'bac', 'convocation', 'radiographies_thoraciques', 'bonne_conduite'] as $doc) {
            if (!$document->$doc) {
                $missing[] = $doc;
            }
        }

        return response()->json([
            'success' => true,
            'student_id' => $student_id,
            'missing_documents' => $missing,
            'count' => count($missing)
        ]);
    }

    // 🚪 إغلاق القسم
    public function closeSection($id)
    {
        $section = Section::findOrFail($id);
        $section->update(['is_closed' => true]);
        return response()->json(['message' => 'تم إغلاق القسم بنجاح']);
    }

    // 👥 عرض جميع التلاميذ داخل قسم
    public function getStudentsBySection($section_id)
    {
        $students = SectionStudent::where('section_id', $section_id)
            ->with('student')
            ->get();
        return response()->json($students);
    }

    // 📆 عرض الغيابات الخاصة بالطالب
    public function getAbsencesByStudent($student_id)
    {
        $absences = Attendance::where('student_id', $student_id)
            ->where('status', 'absent')
            ->get();
        return response()->json($absences);
    }

    // ⚙️ إنشاء قسم جديد تلقائيًا عند انتهاء الحالي
    public function autoCreateNextSection()
    {
        $sections = Section::where('is_closed', false)
            ->where('date_fin', '<', Carbon::now())
            ->get();

        foreach ($sections as $s) {
            $s->update(['is_closed' => true]);
            Section::create([
                'nom' => $s->nom . ' - المرحلة التالية',
                'filiere_id' => $s->filiere_id,
                'date_debut' => Carbon::now(),
                'date_fin' => Carbon::now()->addMonths(6),
                'is_closed' => false,
            ]);
        }

        return response()->json(['message' => 'تم إنشاء الأقسام التالية تلقائيًا']);
    }

    // 📊 توليد تقرير عام
    public function generateReport()
    {
        $report = [
            'total_students' => Students::count(),
            'total_sections' => Section::count(),
            'total_absences' => Attendance::where('status', 'absent')->count(),
        ];
        return response()->json($report);
    }

public function supprimersecstu($id)
{
    // تحقق أولاً إذا كان السجل موجوداً
    $sectionStudent = SectionStudent::find($id);

    if (!$sectionStudent) {
        return response()->json([
            'message' => "Aucun enregistrement trouvé avec l'id=$id."
        ], 404);
    }

    try {
        $sectionStudent->delete();

        return response()->json([
            'message' => "L'enregistrement avec id=$id a été supprimé avec succès."
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => "Erreur lors de la suppression : " . $e->getMessage()
        ], 500);
    }
}
}