<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Section;
use App\Models\SectionStudent;
use Carbon\Carbon;

class UpdateSectionsAndStudents
{
    public function handle($request, Closure $next)
    {
        $today = Carbon::today();

        // الأقسام التي يجب أن تصبح active
        $activeSections = Section::where('status', 'scheduled')
            ->whereDate('start_date', '<=', $today)
            ->get();

        foreach ($activeSections as $section) {
            $section->status = 'active';
            $section->save();
        }

        // الأقسام التي يجب أن تصبح finished
        $finishedSections = Section::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($finishedSections as $section) {
            $section->status = 'finished';
            $section->save();

            // تحديث حالة كل الطلاب النشطين في هذا القسم
            SectionStudent::where('section_id', $section->id)
                ->where('status', 'active')
                ->update(['status' => 'left']); // أو 'expelled' حسب سياستك
        }

        return $next($request);
    }
}
