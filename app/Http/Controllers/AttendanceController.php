<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Section;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // جلب الطلاب حسب القسم
    public function studentsBySection(Section $section)
    {
        $students = $section->students()->get(); // علاقة belongsToMany
        return response()->json($students);
    }

    // جلب الحضور حسب القسم والتاريخ
    public function attendanceBySection(Section $section, Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;

        $attendances = Attendance::with('student')
            ->where('section_id', $section->id)
            ->whereDate('date', $date)
            ->get();

        return response()->json($attendances);
    }

    // حفظ الحضور أو التعديل
    public function saveAttendance(Section $section, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id_stu',
            'attendances.*.present' => 'required|boolean',
            'attendances.*.reason' => 'nullable|string'
        ]);

        $date = $request->date;

        foreach ($request->attendances as $att) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $att['student_id'],
                    'section_id' => $section->id,
                    'date' => $date
                ],
                [
                    'present' => $att['present'],
                    'reason' => $att['reason'] ?? null
                ]
            );
        }

        return response()->json(['message' => 'تم حفظ الحضور بنجاح']);
    }
}
