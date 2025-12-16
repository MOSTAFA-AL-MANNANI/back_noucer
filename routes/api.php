<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Entretien;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionScolaireController;
use App\Http\Controllers\Student;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\AttendanceController;




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/students/import', [StudentImportController::class, 'import']);
// ================= Personel =================
Route::get('/personels', [Entretien::class, 'getPersonels']);
Route::post('/personels', [Entretien::class, 'ajouterPer']);
Route::put('/personels/{id}', [Entretien::class, 'modifierPer']);
Route::delete('/personels/{id}', [Entretien::class, 'supprimerPer']);

// ================= Technique =================
Route::get('/techniques', [Entretien::class, 'getTechniques']);
Route::post('/techniques', [Entretien::class, 'ajouterTech']);
Route::put('/techniques/{id}', [Entretien::class, 'modifierTech']);
Route::delete('/techniques/{id}', [Entretien::class, 'supprimerTech']);

// ================= Students =================
Route::get('/students', [Entretien::class, 'getStudents']);

Route::put('/students/{id}', [Entretien::class, 'modifierStu']);
Route::delete('/students/{id}', [Entretien::class, 'supprimerStu']);

// ================= Resultats =================
Route::get('/resultats', [Entretien::class, 'getResultats']);
Route::post('/resultats', [Entretien::class, 'ajouterResu']);

// ================= Gestion spéciale =================
// ✅ تحديث حالة الطلاب (Top 12 نجاح والباقي انتظار)
Route::post('/students/update-status', [Entretien::class, 'updateStatusTop12']);
Route::get('/students/{id}/detail', [Entretien::class, 'getStudentDetail']);
Route::get('/students/{id}', [Entretien::class, 'show']); // للحصول على بيانات الطالب (بما فيها filiere)

Route::get('/techniques', [Entretien::class, 'index']); // يدعم ?filiere=
Route::controller(GestionScolaireController::class)->group(function () {
    Route::post('/ajouter-filiere', 'ajouterFiliere');
    Route::post('/create-section', 'createSection');
    Route::post('/enroll-student', 'enrollStudent');
    Route::post('/mark-attendance', 'markAttendance');
    Route::post('/ajouter-documents', 'ajouterDocuments');
    Route::get('/check-missing-documents/{student_id}', 'checkMissingDocuments');
    Route::post('/close-section/{id}', 'closeSection');
    Route::get('/students-by-section/{section_id}', 'getStudentsBySection');
    Route::get('/absences-by-student/{student_id}', 'getAbsencesByStudent');
    Route::post('/auto-create-next-section', 'autoCreateNextSection');
    Route::get('/generate-report', 'generateReport');
});

Route::get('/api/students', [GestionScolaireController::class, 'getStudents']);       // عرض كل الشعب
Route::put('/filieres/{id}', [GestionScolaireController::class, 'updateF']); // تعديل شعبة
Route::delete('/filieres/{id}', [GestionScolaireController::class, 'destroyF']); // حذف شعبة

Route::get('/sections', [GestionScolaireController::class, 'indexS']);        // Afficher toutes les sections
Route::post('/sections', [GestionScolaireController::class, 'storeS']);       // Ajouter une section
Route::put('/sections/{id}', [GestionScolaireController::class, 'updateS']);  // Modifier une section
Route::delete('/sections/{id}', [GestionScolaireController::class, 'destroyS']); // Supprimer une section

Route::get('/sections/by-filiere/{filiereName}', [GestionScolaireController::class, 'getSectionsByFiliere']);
Route::post('/enroll-students', [GestionScolaireController::class, 'enrollSelectedStudents']);
Route::get('/top-students', [GestionScolaireController::class, 'getTopStudentsByFiliere']);
Route::post('/enroll-students', [GestionScolaireController::class, 'enrollStudents']);
Route::get('/section/{id}/students', [GestionScolaireController::class, 'getStudentsInSection']);
Route::delete('delete/section/student/{id}', [GestionScolaireController::class, 'supprimersecstu']);

Route::post('/mark-attendance', [GestionScolaireController::class, 'markAttendance']);
Route::get('/attendance/section/{sectionId}/date/{date}', [GestionScolaireController::class, 'getSectionAttendance']);

Route::post('/documents', [GestionScolaireController::class, 'ajouterDocuments']);
Route::get('/documents/missing/{student_id}', [GestionScolaireController::class, 'checkMissingDocuments']);


Route::get('/section/{section}/students', [AttendanceController::class, 'studentsBySection']);
Route::get('/section/{section}/attendance', [AttendanceController::class, 'attendanceBySection']);
Route::post('/section/{section}/attendance', [AttendanceController::class, 'saveAttendance']);

// ✅ جلب الطلاب في حالة انتظار مع النقاط
Route::get('/students/waiting/en', [Entretien::class, 'getWaitingStudentsByFiliere']);
Route::get('/students/filiere/en', [Entretien::class, 'getFilieres']); // للحصول على قائمة الفِرَق
Route::put('/students/status/en/{id}', [Entretien::class, 'updateStatus']);

Route::get('/student/{filiere}', [Student::class, 'getStudents']);
Route::get('/student/{id}/documents', [Student::class, 'getDocuments']);
Route::post('/student/{id}/documents/update', [Student::class, 'updateDocument']);

Route::post('/students/{filiere}/pass-top', [Student::class, 'passTopStudents']);
Route::get('/sections/{id}/students', [Student::class, 'getStudentsBySection']);


// جلب الغيابات لكل طالب
Route::get('/students/{id}/absences', [Student::class, 'getAbsencesByStudent']);
});
Route::get('/top-students/{filiere}', [Entretien::class, 'topStudentsByFiliere']);


Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::get('/filieres', [GestionScolaireController::class, 'indexF']); 
Route::post('/students', [Entretien::class, 'ajouterStu']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
