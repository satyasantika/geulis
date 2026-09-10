<?php

namespace App\Http\Controllers\Riset;

use App\Http\Controllers\Controller;
use App\Models\DataExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EksporController extends Controller
{
    public function __invoke(DataExport $dataExport): BinaryFileResponse
    {
        $path = storage_path('app/'.$dataExport->berkas);
        abort_unless($dataExport->dianonimkan && is_file($path), 404);

        return response()->download($path);
    }
}
