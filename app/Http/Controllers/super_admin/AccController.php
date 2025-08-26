<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;

class AccController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
        ]);
    }

    public function index()
    {
        // GET FOTO
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

        // Urutkan berdasarkan ID (optional)
        usort($foto_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);

        // GET PENDING
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

        // Urutkan berdasarkan ID (optional)
        usort($pending_doc, fn($e, $f) => (int)$e['id'] <=> (int)$f['id']);

        // GET QE
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

        // Urutkan berdasarkan ID (optional)
        usort($qe_doc, fn($g, $h) => (int)$g['id'] <=> (int)$h['id']);

        // GET ACC
        $acc_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $acc_doc = [];
        $tot = 0;
        $grandTotal = 0;
        foreach ($acc_collection as $doca) {
            if ($doca->exists()) {
                $data = $doca->data();

                if (($data['ta_project_status'] ?? '') !== 'ACC') {
                    continue;
                }

                $accFotoRef = $data['ta_project_foto_id']; // Ambil referensi
                $accPendingRef = $data['ta_project_pending_id']; // Ambil referensi
                $accQERef = $data['ta_project_qe_id']; // Ambil referensi

                // Ambil data foto
                $fotoData = null;
                if ($accFotoRef) {
                    $fotoDoc = $accFotoRef->snapshot(); // Ambil snapshot dari referensi
                    if ($fotoDoc->exists()) {
                        $fotoData = $fotoDoc->data();
                    }
                }

                // Ambil data pending
                $pendingData = null;
                if ($accPendingRef) {
                    $pendingDoc = $accPendingRef->snapshot(); // Ambil snapshot dari referensi
                    if ($pendingDoc->exists()) {
                        $pendingData = $pendingDoc->data();
                    }
                }

                // Ambil data QE
                $qeData = null;
                if ($accQERef) {
                    $qeDoc = $accQERef->snapshot(); // Ambil snapshot dari referensi
                    if ($qeDoc->exists()) {
                        $qeData = $qeDoc->data();
                    }
                }

                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);
                $totalValue = (float) ($data['ta_project_total'] ?? 0);

                $acc_doc[] = [
                    'id' => $doca->id(),
                    'nama_project' => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe' => $qeData ? $qeData['type'] : null,
                    'tgl_upload' => $tglUpload,
                    'tgl_pengerjaan' => $tglPengerjaan,
                    'tgl_selesai' => $tglSelesai,
                    'status' => $data['ta_project_status'],
                    'total' => number_format($totalValue, 0, ',', '.'),
                ];

                $tot += (float) ($data['ta_project_total'] ?? 0);
                $grandTotal = number_format($tot, 0, ',', '.');
            }
        }

        // Urutkan berdasarkan ID (optional)
        usort($acc_doc, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);

        return view('super_admin.acc.acc_superadmin', compact('acc_doc', 'grandTotal'));
    }

    public function detail($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // --- Ambil data project utama ---
        $fotoData = $data['ta_project_foto_id'] ? $data['ta_project_foto_id']->snapshot()->data() : null;
        $pendingData = $data['ta_project_pending_id'] ? $data['ta_project_pending_id']->snapshot()->data() : null;
        $qeData = $data['ta_project_qe_id'] ? $data['ta_project_qe_id']->snapshot()->data() : null;

        $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        // --- Ambil detail dari collection Detail_Project_TA ---
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by referensi project
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Ambil data designator dari Data_Project_TA
            $designatorRef = $row['ta_detail_ta_id'];
            $designatorData = $designatorRef->snapshot()->data();

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa = $designatorData['ta_harga_jasa'] ?? 0;
            $volume = $row['ta_detail_volume'] ?? 0;

            $totalM = $hargaMaterial * $volume;
            $totalJ = $hargaJasa * $volume;

            $totalMaterial += $totalM;
            $totalJasa += $totalJ;

            $detail[] = (object)[
                'designator' => $designatorData['ta_designator'] ?? '',
                'uraian' => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan' => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa' => $hargaJasa,
                'volume' => $volume,
                'total_material' => $totalM,
                'total_jasa' => $totalJ,
            ];
        }

        $total = $totalMaterial + $totalJasa;
        $ppn = $total * 0.11;
        $grand = $total - $ppn;

        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $grand],
        ]);

        $totals = [
            'material' => $totalMaterial,
            'jasa' => $totalJasa,
            'total' => $total,
            'ppn' => $ppn,
            'grand' => $grand,
        ];

        return view('super_admin.acc.detail_acc', [
            'acc' => [
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
        if (!$timestamp) return null;

        // kalau Firestore Timestamp, ambil seconds
        if (is_object($timestamp) && method_exists($timestamp, 'get')) {
            $timestamp = $timestamp->get()->format('Y-m-d');
        } else {
            // fallback kalau string/datetime biasa
            $timestamp = Carbon::parse($timestamp)->format('Y-m-d');
        }

        return $timestamp;
    }
}
