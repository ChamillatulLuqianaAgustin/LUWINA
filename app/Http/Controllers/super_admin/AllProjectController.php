<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;

class AllProjectController extends Controller
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

        // GET ALL PROJECT
        $project_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $project_doc = [];
        $grandTotal = 0;
        foreach ($project_collection as $docp) {
            if ($docp->exists()) {
                $data = $docp->data();

                $projectFotoRef = $data['ta_project_foto_id']; // Ambil referensi
                $projectPendingRef = $data['ta_project_pending_id']; // Ambil referensi
                $projectQERef = $data['ta_project_qe_id']; // Ambil referensi

                // Ambil data foto
                $fotoData = null;
                if ($projectFotoRef) {
                    $fotoDoc = $projectFotoRef->snapshot(); // Ambil snapshot dari referensi
                    if ($fotoDoc->exists()) {
                        $fotoData = $fotoDoc->data();
                    }
                }

                // Ambil data pending
                $pendingData = null;
                if ($projectPendingRef) {
                    $pendingDoc = $projectPendingRef->snapshot(); // Ambil snapshot dari referensi
                    if ($pendingDoc->exists()) {
                        $pendingData = $pendingDoc->data();
                    }
                }

                // Ambil data QE
                $qeData = null;
                if ($projectQERef) {
                    $qeDoc = $projectQERef->snapshot(); // Ambil snapshot dari referensi
                    if ($qeDoc->exists()) {
                        $qeData = $qeDoc->data();
                    }
                }

                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

                $project_doc[] = [
                    'id' => $docp->id(),
                    'nama_project' => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe' => $qeData ? $qeData['type'] : null,
                    'tgl_upload' => $tglUpload,
                    'tgl_pengerjaan' => $tglPengerjaan,
                    'tgl_selesai' => $tglSelesai,
                    'status' => $data['ta_project_status'],
                    'total' => $data['ta_project_total'],
                ];

                $grandTotal += (int) ($data['ta_project_total'] ?? 0);
            }
        }

        // Urutkan berdasarkan ID (optional)
        usort($project_doc, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);

        // CHART
        $totalProject = count($project_doc);
        $totalRevenue = array_sum(array_column($project_doc, 'total'));

        $dataPerBulan = array_fill(1, 12, 0);
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $bulan = (int) date('n', strtotime($project['tgl_upload']));
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == 2025) {
                    $dataPerBulan[$bulan]++;
                }
            }
        }
        $chartTotalProjectData = array_values($dataPerBulan);

        $chartQEData = [];
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == date('Y')) {
                    $qe = $project['qe'] ?? 'UNKNOWN';
                    if (!isset($chartQEData[$qe])) {
                        $chartQEData[$qe] = 0;
                    }
                    $chartQEData[$qe]++;
                }
            }
        }

        $chartPieData   = [];
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == date('Y')) {
                    $status = $project['status'] ?? 'UNKNOWN';
                    if (!isset($chartPieData[$status])) {
                        $chartPieData[$status] = 0;
                    }
                    $chartPieData[$status]++;
                }
            }
        }

        return view('super_admin.allproject.allproject_superadmin', compact(
            'project_doc',
            'grandTotal',
            'chartTotalProjectData',
            'chartQEData',
            'chartPieData'
        ));
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
