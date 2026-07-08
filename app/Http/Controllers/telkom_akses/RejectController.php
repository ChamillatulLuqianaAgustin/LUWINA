<?php

namespace App\Http\Controllers\telkom_akses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;
use App\Imports\TaImport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class RejectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase/luwina-ta-firebase-adminsdk-fbsvc-e165a7c4f0.json'),
        ]);
    }

    public function index(Request $request)
    {
        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();

        $start = $request->query('start');
        $end = $request->query('end');

        [$reject_doc, $grandTotal] = $this->fetchRejectData($start, $end);

        return view('telkom_akses.reject.reject_telkomakses', compact('reject_doc', 'grandTotal', 'qe_doc'));
    }

    private function fetchFotoData()
    {
        $foto_collection = $this->getFirestore()->collection('Foto_Evident')->documents();
        $foto_doc = [];

        foreach ($foto_collection as $docf) {
            if ($docf->exists()) {
                $foto_doc[] = [
                    'id' => $docf->id(),
                    'foto' => $docf->data()['foto_path'],
                ];
            }
        }

        usort($foto_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);
        return $foto_doc;
    }

    private function fetchPendingData()
    {
        $pending_collection = $this->getFirestore()->collection('Pending')->documents();
        $pending_doc = [];

        foreach ($pending_collection as $docpe) {
            if ($docpe->exists()) {
                $pending_doc[] = [
                    'id' => $docpe->id(),
                    'keterangan' => $docpe->data()['pending_keterangan'],
                    'waktu' => $docpe->data()['pending_waktu'],
                ];
            }
        }

        usort($pending_doc, fn($e, $f) => (int)$e['id'] <=> (int)$f['id']);
        return $pending_doc;
    }

    private function fetchQEData()
    {
        $qe_collection = $this->getFirestore()->collection('QE')->documents();
        $qe_doc = [];

        foreach ($qe_collection as $docq) {
            if ($docq->exists()) {
                $qe_doc[] = [
                    'id' => $docq->id(),
                    'qe' => $docq->data()['type'],
                ];
            }
        }

        usort($qe_doc, fn($g, $h) => (int)$g['id'] <=> (int)$h['id']);
        return $qe_doc;
    }

    private function fetchRejectData($start = null, $end = null)
    {
        $reject_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $reject_doc = [];
        $tot = 0;

        foreach ($reject_collection as $docr) {
            if ($docr->exists()) {
                $data = $docr->data();

                if (($data['ta_project_status'] ?? '') !== 'REJECT') {
                    continue;
                }

                // Ambil tanggal upload
                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);

                // Jika user memilih rentang tanggal → filter
                if ($start && $end) {
                    try {
                        $uploadDate = Carbon::parse($tglUpload);
                        $startDate = Carbon::parse($start);
                        $endDate = Carbon::parse($end);

                        // Jika tglUpload di luar rentang, skip
                        if ($uploadDate->lt($startDate) || $uploadDate->gt($endDate)) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        continue; // kalau parsing gagal, skip aja
                    }
                }

                $rejectQERef = $data['ta_project_qe_id'];

                $qeData = $this->getReferenceData($rejectQERef);

                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);
                $totalValue = (float) ($data['ta_project_total'] ?? 0);

                $reject_doc[] = [
                    'id' => $docr->id(),
                    'nama_project' => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe' => $qeData ? $qeData['type'] : null,
                    'tgl_upload' => $tglUpload,
                    'tgl_pengerjaan' => $tglPengerjaan,
                    'tgl_selesai' => $tglSelesai,
                    'status' => $data['ta_project_status'],
                    'total' => number_format($totalValue, 0, ',', '.'),
                ];

                $tot += $totalValue;
            }
        }

        return [$reject_doc, number_format($tot, 0, ',', '.')];
    }

    private function fetchProjectTaData()
    {
        return Cache::remember('project_mitra_doc', 3600, function () {
            $project_mitra_collection = $this->getFirestore()->collection('Data_Project_Mitra')->documents();

            $project_mitra_doc = [];
            $uraianOptions = [];
            foreach ($project_mitra_collection as $docd) {
                if ($docd->exists()) {
                    $project_mitra_doc[] = [
                        'id' => $docd->id(),
                        'designator' => $docd->data()['mitra_designator'],
                        'uraian' => $docd->data()['mitra_uraian_pekerjaan'],
                        'satuan' => $docd->data()['mitra_satuan'],
                        'harga_material' => $docd->data()['mitra_harga_material'],
                        'harga_jasa' => $docd->data()['mitra_harga_jasa'],
                    ];
                    $uraianOptions[] = $docd->data()['mitra_uraian_pekerjaan'];
                }
            }

            $uraianOptions = array_values(array_unique($uraianOptions));
            sort($uraianOptions);
            usort($project_mitra_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);

            return [$project_mitra_doc, $uraianOptions];
        });
    }

    private function getReferenceData($ref)
    {
        if ($ref && method_exists($ref, 'snapshot')) {
            $doc = $ref->snapshot();
            return $doc->exists() ? $doc->data() : null;
        }
        return null;
    }

    private function hitungTotal($detailDocs)
    {
        $total = 0;

        foreach ($detailDocs as $d) {

            if (!$d->exists()) continue;

            $row = $d->data();

            $designatorData = $row['ta_detail_ta_id']->snapshot()->data();

            $volume = $row['ta_detail_volume'] ?? 0;

            // ==========================
            // Cek apakah project upload excel
            // ==========================

            if (
                isset($row['ta_detail_harga_material']) &&
                isset($row['ta_detail_harga_jasa'])
            ) {

                $hargaMaterial = $row['ta_detail_harga_material'];
                $hargaJasa     = $row['ta_detail_harga_jasa'];
            } else {

                $hargaMaterial = $designatorData['mitra_harga_material'] ?? 0;
                $hargaJasa     = $designatorData['mitra_harga_jasa'] ?? 0;
            }

            $total += ($hargaMaterial + $hargaJasa) * $volume;
        }

        return [
            'material' => 0,
            'jasa'     => 0,
            'total'    => $total,
        ];
    }

    public function detail($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('telkomakses.reject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();
        $fotoData = $data['ta_project_foto'] ?? [];
        $pendingData = $this->getReferenceData($data['ta_project_pending_id'] ?? null);
        $qeData = $this->getReferenceData($data['ta_project_qe_id'] ?? null);

        $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by project reference
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Fetch data dari Data_Project_Mitra
            $designatorRef  = $row['ta_detail_ta_id'];
            $designatorData = $this->getReferenceData($designatorRef);

            // ==========================
            // Cek apakah upload Excel
            // ==========================
            if (
                isset($row['ta_detail_harga_material']) &&
                isset($row['ta_detail_harga_jasa'])
            ) {

                // gunakan harga dari Detail_Project_TA
                $hargaMaterial = $row['ta_detail_harga_material'];
                $hargaJasa     = $row['ta_detail_harga_jasa'];
            } else {

                // gunakan harga master
                $hargaMaterial = $designatorData['mitra_harga_material'] ?? 0;
                $hargaJasa     = $designatorData['mitra_harga_jasa'] ?? 0;
            }

            $volume = $row['ta_detail_volume'] ?? 0;

            $totalM = $hargaMaterial * $volume;
            $totalJ = $hargaJasa * $volume;

            $totalMaterial += $totalM;
            $totalJasa += $totalJ;

            $detail[] = (object)[
                'id' => $d->id(),
                'designator' => $designatorData['mitra_designator'] ?? '',
                'uraian' => $designatorData['mitra_uraian_pekerjaan'] ?? '',
                'satuan' => $designatorData['mitra_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa' => $hargaJasa,
                'volume' => $volume,
                'total_material' => $totalM,
                'total_jasa' => $totalJ,
            ];
        }

        $total = $totalMaterial + $totalJasa;
        // $ppn = $total * 0.11;
        // $grand = $total + $ppn;

        // Update project total in Firestore
        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $total],
        ]);

        $totals = [
            'material' => $totalMaterial,
            'jasa' => $totalJasa,
            'total' => $total,
            // 'ppn' => $ppn,
            // 'grand' => $grand,
        ];

        return view('telkom_akses.reject.detail_reject', [
            'reject' => [
                'id' => $id,
                'nama_project' => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe' => $qeData['type'] ?? null,
                'foto' => $fotoData,
                'pending' => $pendingData,
                'tgl_upload' => $tglUpload,
                'tgl_pengerjaan' => $tglPengerjaan,
                'tgl_selesai' => $tglSelesai,
                'status' => $data['ta_project_status'],
                'total' => $data['ta_project_total'],
                'detail' => $detail,
            ],
            'totals' => $totals,
        ]);
    }

    private function formatDate($timestamp)
    {
        if (!$timestamp) {
            return "-";
        };

        if (is_object($timestamp) && method_exists($timestamp, 'get')) {
            $timestamp = $timestamp->get()->format('Y-m-d');
        } else {
            $timestamp = Carbon::parse($timestamp)->format('Y-m-d');
        }

        return $timestamp;
    }
}
