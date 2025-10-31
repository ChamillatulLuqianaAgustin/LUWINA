<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp as FireTimestamp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


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
        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();
        list($acc_doc, $grandTotal) = $this->fetchAccProjects();

        return view('super_admin.acc.acc_superadmin', compact('acc_doc', 'grandTotal'));
    }

    private function fetchFotoData()
    {
        $foto_collection = $this->getFirestore()->collection('Foto_Evident')->documents();
        $foto_doc = [];

        foreach ($foto_collection as $docf) {
            if ($docf->exists()) {
                $paths = $docf->data()['foto_path'] ?? [];

                if (!is_array($paths)) {
                    $paths = [$paths];
                }

                foreach ($paths as $path) {
                    $foto_doc[] = [
                        'id' => $docf->id(),
                        'foto' => $path,
                    ];
                }
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

    private function fetchAccProjects()
    {
        $acc_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $acc_doc = [];
        $tot = 0;

        foreach ($acc_collection as $doca) {
            if ($doca->exists()) {
                $data = $doca->data();

                if (($data['ta_project_status'] ?? '') !== 'ACC') {
                    continue;
                }

                $accFotoRef = $data['ta_project_foto_id'];
                $accPendingRef = $data['ta_project_pending_id'];
                $accQERef = $data['ta_project_qe_id'];

                $fotoData = $this->getReferenceData($accFotoRef);
                $pendingData = $this->getReferenceData($accPendingRef);
                $qeData = $this->getReferenceData($accQERef);

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

                $tot += $totalValue;
            }
        }

        return [$acc_doc, number_format($tot, 0, ',', '.')];
    }

    private function getReferenceData($ref)
    {
        if ($ref && method_exists($ref, 'snapshot')) {
            $doc = $ref->snapshot();
            return $doc->exists() ? $doc->data() : null;
        }
        return null;
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

        // --- Foto evident (ambil semua dokumen by project_id)
        $fotoDocs = $firestore->collection('Foto_Evident')
            ->where('project_id', '=', $id)
            ->documents();

        $fotoData = [
            'sebelum' => [],
            'proses' => [],
            'sesudah' => [],
        ];

        foreach ($fotoDocs as $docFoto) {
            if ($docFoto->exists()) {
                $dataFoto = $docFoto->data()['foto_path'] ?? [];

                if (is_object($dataFoto)) {
                    $dataFoto = json_decode(json_encode($dataFoto), true);
                }

                foreach (['sebelum', 'proses', 'sesudah'] as $step) {
                    if (!empty($dataFoto[$step])) {
                        $fotoData[$step] = array_merge($fotoData[$step], $dataFoto[$step]);
                    }
                }
            }
            // dd($docFoto->data());
        }

        $acc['foto'] = $fotoData;

        // --- Pending (ambil semua dokumen by project_id)
        $pendingDocs = $firestore->collection('Pending')
            ->where('project_id', '=', $id)->documents();
        $pendingData = [];
        foreach ($pendingDocs as $pd) {
            if (!$pd->exists()) continue;
            $dataPd = $pd->data();
            $kets = $dataPd['pending_keterangan'] ?? null;
            $waktus = $dataPd['pending_waktu'] ?? null;

            if (is_array($kets)) {
                foreach ($kets as $i => $ket) {
                    $pendingData[] = [
                        'tgl_pending' => is_array($waktus) ? ($waktus[$i] ?? $waktus[0] ?? '-') : ($waktus ?? '-'),
                        'keterangan'  => $ket ?? '-',
                    ];
                }
            } else {
                $pendingData[] = [
                    'tgl_pending' => $waktus ?? '-',
                    'keterangan'  => $kets ?? '-',
                ];
            }
        }

        // Fetch detail from Detail_Project_TA
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by project reference
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Fetch designator data
            $designatorRef = $row['ta_detail_ta_id'];
            $designatorData = $this->getReferenceData($designatorRef);

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

        // Update project total in Firestore
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

        // // 🔍 DEBUG CEK DATA FOTO DAN PENDING
        // dd([
        //     'id_project' => $id,
        //     'fotoData' => $fotoData,
        //     'pendingData' => $pendingData,
        // ]);


        return view('super_admin.acc.detail_acc', [
            'acc' => [
                'id'              => $id,
                'nama_project'    => $data['ta_project_pekerjaan'],
                'deskripsi_project'=> $data['ta_project_deskripsi'],
                'qe'              => $data['ta_project_qe_id'] ?? null,
                'foto'            => $fotoData,
                'pending'         => $pendingData,
                'tgl_upload'      => $this->formatDate($data['ta_project_waktu_upload'] ?? null),
                'tgl_pengerjaan'  => $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null),
                'tgl_selesai'     => $this->formatDate($data['ta_project_waktu_selesai'] ?? null),
                'status'          => $data['ta_project_status'],
                'total'           => $data['ta_project_total'],
                'detail'          => $detail,
            ],
            'totals' => $totals,
        ]);
    }

    public function kerjakan($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);

        // cek apakah dokumen ada
        $doc = $docRef->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')
                            ->with('error', 'Project tidak ditemukan');
        }

        // gunakan Firestore Timestamp agar konsisten dengan data Firestore
        $now = new FireTimestamp(new \DateTime());

        $docRef->update([
            ['path' => 'ta_project_waktu_pengerjaan', 'value' => $now],
        ]);

        return redirect()->route('superadmin.acc_detail', $id)
                        ->with('success', 'Tanggal pengerjaan berhasil diset.');
    }

    public function storeFoto(Request $request, $id)
    {
        $firestore = $this->getFirestore();

        // cari dokumen foto berdasarkan project_id
        $fotoDocs = $firestore->collection('Foto_Evident')
            ->where('project_id', '=', $id)
            ->documents();

        // kalau belum ada dokumen untuk project ini → buat baru
        $docRef = null;
        foreach ($fotoDocs as $doc) {
            if ($doc->exists()) {
                $docRef = $firestore->collection('Foto_Evident')->document($doc->id());
                break;
            }
        }

        if (!$docRef) {
            // bikin dokumen baru
            $docRef = $firestore->collection('Foto_Evident')->add([
                'project_id' => $id,
                'foto_path'  => [
                    'sebelum' => [],
                    'proses' => [],
                    'sesudah' => [],
                ],
                'uploaded_at' => new FireTimestamp(new \DateTime())
            ]);
            $docRef = $firestore->collection('Foto_Evident')->document($docRef->id());
        }

        // ambil data lama biar ga kehapus
        $fotoData = $docRef->snapshot()->data()['foto_path'] ?? [
            'sebelum' => [],
            'proses'  => [],
            'sesudah' => [],
        ];

        // upload per kategori
        foreach (['sebelum', 'proses', 'sesudah'] as $tipe) {
            $inputName = 'foto_' . $tipe;
            if ($request->hasFile($inputName)) {
                foreach ($request->file($inputName) as $file) {
                    $path = $file->store("uploads/foto/{$tipe}", 'public');
                    $fotoData[$tipe][] = asset('storage/' . $path);
                }
            }
        }

        // update data di Firestore
        $docRef->update([
            ['path' => 'foto_path', 'value' => $fotoData],
            ['path' => 'uploaded_at', 'value' => new FireTimestamp(new \DateTime())],
        ]);

        // 🚀 Tambahan: update project utama supaya "Done" hanya bisa sekali
        $projectRef = $firestore->collection('All_Project_TA')->document($id);
        $projectDoc = $projectRef->snapshot();

        if ($projectDoc->exists()) {
            // Jika belum selesai → set tanggal selesai
            $data = $projectDoc->data();
            if (empty($data['ta_project_waktu_selesai'])) {
                $projectRef->update([
                    ['path' => 'ta_project_waktu_selesai', 'value' => new FireTimestamp(new \DateTime())],
                ]);
            }
        }

        return back()->with('success', 'Foto evident berhasil diupload.');
    }

    public function pending(Request $request, $id)
    {
        $request->validate([
            'tgl_pending'   => 'required|array|min:1',
            'tgl_pending.*' => 'required|date',
            'keterangan'    => 'required|array|min:1',
            'keterangan.*'  => 'required|string|max:255',
        ]);

        $firestore = $this->getFirestore();

        foreach ($request->keterangan as $i => $ket) {
            $tgl = $request->tgl_pending[$i] ?? $request->tgl_pending[0] ?? now()->format('Y-m-d');

            $pendingRef = $firestore->collection('Pending')->add([
                'pending_keterangan' => $ket,
                'pending_waktu'      => $tgl,
                'project_id'         => $id,
                'created_at'         => new FireTimestamp(new \DateTime())
            ]);
        }

        return back()->with('success', 'Project berhasil dipending');
    }

    private function formatDate($timestamp)
{
    // Jika null, kosong, atau tidak valid
    if (empty($timestamp) || $timestamp === '0000-00-00') {
        return '-';
    }

    try {
        // Firestore Timestamp
        if ($timestamp instanceof \Google\Cloud\Core\Timestamp) {
            return $timestamp->get()->format('Y-m-d');
        }

        // DateTime / Carbon instance
        if ($timestamp instanceof \DateTimeInterface) {
            return Carbon::instance($timestamp)->format('Y-m-d');
        }

        // String valid (cek parseable)
        $date = Carbon::parse($timestamp);
        return $date->format('Y-m-d');
    } catch (\Exception $e) {
        // Kalau parsing gagal, tampilkan "-"
        return '-';
    }
}
}
