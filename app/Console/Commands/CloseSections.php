<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Section;
use Carbon\Carbon;

class CloseSections extends Command
{
    protected $signature = 'sections:close';
    protected $description = 'Fermer automatiquement les sections dont la date de fin est dépassée';

    public function handle()
    {
        $today = Carbon::tomorrow();

        // Trouver toutes les sections dont end_date <= aujourd'hui et status != finished
        $sections = Section::whereDate('end_date', '<=', $today)
                            ->where('status', '!=', 'finished')
                            ->get();

        foreach ($sections as $section) {
            $section->status = 'finished';
            $section->save();
            $this->info("Section {$section->name} fermée.");
        }

        $this->info('Toutes les sections terminées ont été mises à jour.');
    }
}
