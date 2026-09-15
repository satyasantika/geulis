<?php

namespace App\Services\Research;

use App\Models\AdaptationLog;
use App\Models\ExpertValidation;
use App\Models\MasteryState;
use App\Models\MotifSubmission;
use App\Models\Observation;
use App\Models\Questionnaire;
use App\Models\QuestionnaireReflection;
use App\Models\QuestionnaireResponse;
use App\Models\Reflection;
use App\Models\User;
use App\Services\Motif\PenilaiMotif;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

/**
 * Ekspor data siap analisis (.xlsx multi-lembar). SELALU memakai kode anonim
 * (aturan #4): tidak ada nama, NIS, atau surel siswa di lembar mana pun.
 * Hanya siswa yang bersedia diteliti yang diekspor. Pemetaan kode → identitas
 * hanya di basis data, untuk ketua peneliti.
 */
final class PengeksporData
{
    public function __construct(
        private readonly AnalitikPenelitian $analitik,
        private readonly PenilaiMotif $penilaiMotif,
    ) {}

    /**
     * @return array<string, list<list<mixed>>> nama lembar → baris (baris pertama judul kolom)
     */
    public function lembar(): array
    {
        if (! config('geulis.anonimkan_ekspor', true)) {
            throw new RuntimeException('anonimkan_ekspor harus true. Ekspor beridentitas tidak disediakan.');
        }

        $siswa = User::query()->bersediaDiteliti()->whereHas('roles', fn ($q) => $q->where('nama', 'siswa'))
            ->with(['placement', 'enrollments.classroom.school', 'ctScores.test'])->orderBy('kode_anonim')->get();
        $kode = $siswa->pluck('kode_anonim', 'id');
        $idSiswa = $siswa->pluck('id');

        $lembar = [];

        $lembar['siswa'] = [['kode', 'sekolah', 'kelas', 'kelompok', 'jenis_kelamin', 'skor_readiness', 'level_awal', 'modus', 'artefak_utama', 'level_akhir'],
            ...$siswa->map(function (User $s) {
                $e = $s->enrollments->firstWhere('status', 'aktif') ?? $s->enrollments->first();

                return [$s->kode_anonim, $e?->classroom?->school?->nama, $e?->classroom?->nama, $e?->classroom?->kelompok_riset, $s->jenis_kelamin,
                    $s->placement?->skor_readiness, $s->placement?->level_awal, $s->placement?->modus, $s->placement?->artefak_utama, $e?->level_kini];
            })->all()];

        foreach (['pretest' => 'ct_pretest', 'posttest' => 'ct_posttest'] as $jenis => $nama) {
            $lembar[$nama] = [['kode', 'skor_d', 'skor_p', 'skor_a', 'skor_al', 'skor_total', 'persen', 'lengkap'],
                ...$siswa->flatMap(fn (User $s) => $s->ctScores->filter(fn ($c) => $c->test->jenis === $jenis && $c->dikumpulkan_pada !== null)
                    ->map(fn ($c) => [$s->kode_anonim, $c->skor_d, $c->skor_p, $c->skor_a, $c->skor_al, $c->skor_total, $c->persen, $c->dihitung_pada ? 1 : 0]))->all()];
        }

        $lembar['ngain'] = [['kode', 'kelompok', 'pretest', 'posttest', 'n_gain', 'kategori', 'ngain_d', 'ngain_p', 'ngain_a', 'ngain_al'],
            ...$this->analitik->perSiswa()->map(fn ($b) => [$b['siswa']->kode_anonim, $b['kelompok'], $b['pretest'], $b['posttest'], $b['n_gain'], $b['kategori'],
                $b['per_indikator']['D'], $b['per_indikator']['P'], $b['per_indikator']['A'], $b['per_indikator']['Al']])->all()];

        $lembar['mastery'] = [['kode', 'pertemuan', 'unit', 'nilai_m', 'level_saat_itu', 'iterasi_remedial', 'perlu_pendampingan'],
            ...MasteryState::query()->with('lessonUnit.meeting')->whereIn('user_id', $idSiswa)->get()
                ->map(fn ($m) => [$kode[$m->user_id], $m->lessonUnit->meeting->urutan, $m->lessonUnit->judul, $m->nilai_m, $m->level_saat_itu, $m->iterasi_remedial, $m->perlu_pendampingan ? 1 : 0])->all()];

        $rekapAdaptasi = AdaptationLog::query()->whereIn('user_id', $idSiswa)->get()->groupBy('user_id');
        $lembar['adaptasi'] = [['kode', 'n_keputusan', 'promote', 'enrich', 'reinforce', 'remediate', 'escalate', 'guess_guard'],
            ...$siswa->map(function (User $s) use ($rekapAdaptasi) {
                $g = $rekapAdaptasi->get($s->id, collect());
                $hitung = fn (string $k) => $g->where('kode_aturan', $k)->count();

                return [$s->kode_anonim, $g->count(), $hitung('RULE_PROMOTE'), $hitung('RULE_ENRICH'), $hitung('RULE_REINFORCE'), $hitung('RULE_REMEDIATE'), $hitung('RULE_ESCALATE'), $hitung('RULE_GUESS_GUARD')];
            })->all()];

        $lembar['motif'] = [['kode', 'pertemuan', 'percobaan_ke', 'skor_kemiripan', 'lolos', 'langkah_siswa', 'langkah_minimum', 'efisiensi', 'memakai_perulangan', 'dekomposisi_tepat', 'pola_benar', 'abstraksi_proporsi'],
            ...MotifSubmission::query()->with('activity.lessonUnit.meeting')->whereIn('user_id', $idSiswa)->get()->map(function (MotifSubmission $m) use ($kode) {
                $bukti = $this->penilaiMotif->buktiCT($m);

                return [$kode[$m->user_id], $m->activity->lessonUnit->meeting->urutan, $m->percobaan_ke, $m->skor_kemiripan, $m->lolos ? 1 : 0, $m->langkah_siswa, $m->langkah_minimum, $m->efisiensi,
                    $bukti['algoritma']['memakai_perulangan'] ? 1 : 0, $bukti['dekomposisi']['tepat'], $bukti['pengenalan_pola']['benar'] ? 1 : 0, $bukti['abstraksi']['proporsi']];
            })->all()];

        foreach (['siswa' => 'angket_siswa', 'guru' => 'angket_guru'] as $sasaran => $nama) {
            $angket = Questionnaire::query()->with('items')->where('sasaran', $sasaran)->where('jenis', 'kepraktisan')->first();
            $baris = [];
            if ($angket !== null) {
                $respons = QuestionnaireResponse::query()->whereIn('questionnaire_item_id', $angket->items->pluck('id'))
                    ->when($sasaran === 'siswa', fn ($q) => $q->whereIn('user_id', $idSiswa))->get()->groupBy('user_id');
                foreach ($respons as $userId => $jawaban) {
                    $baris[] = [$sasaran === 'siswa' ? $kode[$userId] : 'G-'.$userId, ...$angket->items->map(fn ($i) => $jawaban->firstWhere('questionnaire_item_id', $i->id)?->skor)->all()];
                }
            }
            $lembar[$nama] = [['kode', ...($angket?->items->map(fn ($i) => 'b'.$i->urutan)->all() ?? [])], ...$baris];
        }

        $angketPersepsi = Questionnaire::query()->with('items')->where('sasaran', 'siswa')->where('jenis', 'persepsi')->first();
        $barisPersepsi = [];
        if ($angketPersepsi !== null) {
            $respons = QuestionnaireResponse::query()->whereIn('questionnaire_item_id', $angketPersepsi->items->pluck('id'))
                ->whereIn('user_id', $idSiswa)->get()->groupBy('user_id');
            $catatan = QuestionnaireReflection::query()->where('questionnaire_id', $angketPersepsi->id)
                ->whereIn('user_id', $idSiswa)->get()->groupBy('user_id');
            foreach ($respons as $userId => $jawaban) {
                $c = $catatan->get($userId, collect());
                $barisPersepsi[] = [$kode[$userId], ...$angketPersepsi->items->map(fn ($i) => $jawaban->firstWhere('questionnaire_item_id', $i->id)?->skor)->all(),
                    $c->firstWhere('kode', 'disukai')?->jawaban, $c->firstWhere('kode', 'diperbaiki')?->jawaban, $c->firstWhere('kode', 'saran')?->jawaban];
            }
        }
        $lembar['angket_persepsi'] = [['kode', ...($angketPersepsi?->items->map(fn ($i) => 'b'.$i->urutan)->all() ?? []), 'disukai', 'diperbaiki', 'saran'], ...$barisPersepsi];

        $lembar['validasi_ahli'] = [['validator', 'aspek', 'butir', 'pernyataan', 'skor', 'saran'],
            ...ExpertValidation::query()->with(['ratings.item'])->where('status', 'selesai')->get()
                ->flatMap(fn (ExpertValidation $v) => $v->ratings->map(fn ($r) => ['V-'.$v->validator_id, $r->item->aspek, $r->item->urutan, $r->item->pernyataan, $r->skor, $r->saran]))->all()];

        $lembar['observasi'] = [['observer', 'sekolah', 'kelas', 'pertemuan', 'tanggal', 'butir', 'skor', 'catatan_lapangan'],
            ...Observation::query()->with(['classroom.school', 'meeting', 'records.item'])->get()
                ->flatMap(fn (Observation $o) => $o->records->map(fn ($r) => ['O-'.$o->observer_id, $o->classroom->school?->nama, $o->classroom->nama, $o->meeting->urutan, $o->tanggal->toDateString(), $r->item->urutan, $r->skor, $o->catatan_lapangan]))->all()];

        $lembar['refleksi'] = [['kode', 'pertemuan', 'pertanyaan', 'jawaban'],
            ...Reflection::query()->with('meeting')->whereIn('user_id', $idSiswa)->get()->map(fn ($r) => [$kode[$r->user_id], $r->meeting->urutan, $r->pertanyaan, $r->jawaban])->all()];

        return $lembar;
    }

    /** Menulis seluruh lembar ke berkas .xlsx; mengembalikan path absolut. */
    public function tulis(string $path): string
    {
        $lembar = $this->lembar();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = new Writer;
        $writer->openToFile($path);
        $pertama = true;
        foreach ($lembar as $nama => $baris) {
            $sheet = $pertama ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $sheet->setName($nama);
            $pertama = false;
            foreach ($baris as $b) {
                $writer->addRow(Row::fromValues(array_map(fn ($v) => $v instanceof \Stringable ? (string) $v : $v, $b)));
            }
        }
        $writer->close();

        return $path;
    }

    /** Nama & NIS tidak boleh muncul: dipakai uji dan pemeriksaan sebelum berkas disimpan. */
    public function mengandungIdentitas(array $lembar, Collection $siswa): bool
    {
        $terlarang = $siswa->flatMap(fn (User $s) => [$s->nama, $s->username])->filter()->all();
        foreach ($lembar as $baris) {
            foreach ($baris as $b) {
                foreach ($b as $sel) {
                    if (is_string($sel) && in_array($sel, $terlarang, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
