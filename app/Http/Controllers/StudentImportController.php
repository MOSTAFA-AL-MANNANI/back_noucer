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

        // التحقق من امتداد ZipArchive إذا كان الملف xlsx أو xls
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

        $headers = array_map(function($h) {
            return strtolower(trim($h));
        }, $rows[0]);

        unset($rows[0]);

        foreach ($rows as $row) {
            $data = [];

            foreach ($headers as $index => $header) {
                $value = $row[$index] ?? null;

                if (in_array($header, ['nom', 'name'])) $data['nom'] = $value;
                elseif (in_array($header, ['prenom', 'prénom'])) $data['prenom'] = $value;
                elseif ($header === 'cin') $data['cin'] = $value;
                elseif (in_array($header, ['genre', 'sex'])) $data['genre'] = $value;
                elseif (in_array($header, ['gmail', 'email'])) $data['gmail'] = $value;
                elseif (in_array($header, ['numero', 'phone', 'téléphone'])) $data['numero'] = $value;
                elseif (in_array($header, ['adresse', 'address'])) $data['adresse'] = $value;
                elseif (in_array($header, ['niveau_sco', 'niveau'])) $data['niveau_sco'] = $value;
                elseif (in_array($header, ['date_naissance', 'date naissance', 'birthdate']) && !empty($value)) {
                    $data['date_naissance'] = Carbon::parse($value)->format('Y-m-d');
                }
            }

            $data['filiere'] = $filiere;
            $data['status'] = 'registred';

            Students::create($data);
        }

        return response()->json(['message' => 'Import terminé']);
    }
}
