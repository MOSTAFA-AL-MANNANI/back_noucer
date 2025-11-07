<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Students;
use Carbon\Carbon;

class StudentImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'filiere' => 'required|string',
        ]);

        $filiere = $request->filiere;
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        // تحقق من ZipArchive إذا الملف Excel
        if (in_array($extension, ['xlsx', 'xls']) && !class_exists('ZipArchive')) {
            return response()->json([
                'message' => "Erreur : L'importation des fichiers $extension nécessite l'extension PHP ZipArchive activée."
            ], 500);
        }

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la lecture du fichier Excel : " . $e->getMessage()
            ], 500);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            return response()->json(['message' => 'Fichier Excel vide ou sans données.'], 400);
        }

        $headers = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
        unset($rows[0]);

        // تحقق أن العمود niveau_sco موجود في الملف
        if (!in_array('niveau_sco', $headers) && !in_array('niveau', $headers) && !in_array('niveau scolaire', $headers)) {
            return response()->json([
                'message' => "Le fichier Excel doit contenir une colonne 'niveau_sco' (ou 'Niveau' / 'Niveau Scolaire')."
            ], 400);
        }

        $inserted = 0;
        $skipped = [];

        foreach ($rows as $index => $row) {
            $data = [];

            foreach ($headers as $i => $header) {
                $value = $row[$i] ?? null;

                if (in_array($header, ['nom','name'])) $data['nom'] = $value;
                elseif (in_array($header, ['prenom','prénom'])) $data['prenom'] = $value;
                elseif ($header === 'cin') $data['cin'] = $value;
                elseif (in_array($header, ['genre','sex'])) $data['genre'] = $value;
                elseif (in_array($header, ['gmail','email'])) $data['gmail'] = $value;
                elseif (in_array($header, ['numero','phone','téléphone'])) $data['numero'] = $value;
                elseif (in_array($header, ['adresse','address'])) $data['adresse'] = $value;
                elseif (in_array($header, ['niveau_sco','niveau','niveau scolaire'])) $data['niveau_sco'] = $value;
                elseif (in_array($header, ['date_naissance','date naissance','birthdate']) && !empty($value)) {
                    try {
                        $data['date_naissance'] = Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $data['date_naissance'] = null;
                    }
                }
            }

            // تحقق أن niveau_sco موجود وله قيمة
            if (empty($data['niveau_sco'])) {
                $skipped[] = $index + 2; // رقم الصف في Excel (الصف 1 رؤوس)
                continue;
            }

            $data['filiere'] = $filiere;
            $data['status'] = 'registred';

            Students::create($data);
            $inserted++;
        }

        if (!empty($skipped)) {
            return response()->json([
                'message' => "Import terminé partiellement: $inserted lignes insérées, certaines lignes ignorées car 'niveau_sco' est vide.",
                'lignes_ignorées' => $skipped
            ]);
        }

        return response()->json(['message' => "Import terminé avec succès ($inserted lignes ajoutées)."]);
    }
}
