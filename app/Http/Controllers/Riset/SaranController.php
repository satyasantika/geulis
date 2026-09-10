<?php

namespace App\Http\Controllers\Riset;

use App\Http\Controllers\Controller;
use App\Models\ValidationRating;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Rekap saran validator sebagai daftar tugas revisi (.csv). */
class SaranController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $saran = ValidationRating::query()->with(['item', 'validation.validator'])
            ->whereNotNull('saran')->where('saran', '!=', '')
            ->whereHas('validation', fn ($q) => $q->where('status', 'selesai'))
            ->get()->sortBy(fn ($r) => $r->item->urutan);

        return response()->streamDownload(function () use ($saran): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['no', 'aspek', 'butir', 'pernyataan', 'skor', 'saran', 'validator', 'status_revisi'], ';', '"', '\\');
            foreach ($saran as $i => $r) {
                fputcsv($out, [$i + 1, $r->item->aspek, $r->item->urutan, $r->item->pernyataan, $r->skor, $r->saran, 'V-'.$r->validation->validator_id, 'belum'], ';', '"', '\\');
            }
            fclose($out);
        }, 'daftar-revisi-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
