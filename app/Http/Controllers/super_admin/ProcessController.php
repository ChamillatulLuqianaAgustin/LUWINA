<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;

class ProcessController extends Controller
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

        // GET PROCESS
        $process_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $process_doc = [];
        $grandTotal = 0;
        foreach ($process_collection as $docp) {
            if ($docp->exists()) {
                $data = $docp->data();

                if (($data['ta_project_status'] ?? '') !== 'PROCESS') {
                    continue;
                }

                $processFotoRef = $data['ta_project_foto_id']; // Ambil referensi
                $processPendingRef = $data['ta_project_pending_id']; // Ambil referensi
                $processQERef = $data['ta_project_qe_id']; // Ambil referensi

                // Ambil data foto
                $fotoData = null;
                if ($processFotoRef) {
                    $fotoDoc = $processFotoRef->snapshot(); // Ambil snapshot dari referensi
                    if ($fotoDoc->exists()) {
                        $fotoData = $fotoDoc->data();
                    }
                }

                // Ambil data pending
                $pendingData = null;
                if ($processPendingRef) {
                    $pendingDoc = $processPendingRef->snapshot(); // Ambil snapshot dari referensi
                    if ($pendingDoc->exists()) {
                        $pendingData = $pendingDoc->data();
                    }
                }

                // Ambil data QE
                $qeData = null;
                if ($processQERef) {
                    $qeDoc = $processQERef->snapshot(); // Ambil snapshot dari referensi
                    if ($qeDoc->exists()) {
                        $qeData = $qeDoc->data();
                    }
                }

                $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

                $process_doc[] = [
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
        usort($process_doc, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);

        return view('super_admin.process.process_superadmin', compact('process_doc', 'grandTotal'));
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
