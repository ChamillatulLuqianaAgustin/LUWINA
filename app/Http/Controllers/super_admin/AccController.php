<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp as FireTimestamp;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Cloudinary\Cloudinary;
// use Google\Cloud\Firestore\DocumentSnapshot;
// use Illuminate\Support\Str;

class AccController extends Controller
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
        $start = $request->query('start');
        $end = $request->query('end');

        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();
        list($acc_doc, $grandTotal) = $this->fetchAccProjects($start, $end);

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

    private function fetchAccProjects($start = null, $end = null)
    {
        $acc_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $acc_doc = [];
        $tot = 0;

        foreach ($acc_collection as $doca) {
            if ($doca->exists()) {
                $data = $doca->data();

                $status = $data['ta_project_status'] ?? '';

                if (!in_array($status, ['ACC', 'REKONSILIASI', 'REVIEW TA', 'CLOSE'])) {
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

                // $accFotoRef = $data['ta_project_foto_id'];
                // $accPendingRef = $data['ta_project_pending_id'];
                $accQERef = $data['ta_project_qe_id'];

                // $fotoData = $this->getReferenceData($accFotoRef);
                // $pendingData = $this->getReferenceData($accPendingRef);
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

        $returnDocs = $firestore->collection('Return_Project')
            ->where('project_id', '=', $id)
            ->documents();

        $catatanReturn = null;

        foreach ($returnDocs as $rd) {
            if ($rd->exists()) {
                $catatanReturn = $rd->data()['catatan'] ?? null;
                break; // ambil yang pertama
            }
        }

        return view('super_admin.acc.detail_acc', [
            'acc' => [
                'id'              => $id,
                'nama_project'    => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe'              => $data['ta_project_qe_id'] ?? null,
                'foto'            => $fotoData,
                'foto_project' => $data['ta_project_foto'] ?? [],
                'pending'         => $pendingData,
                'tgl_upload'      => $this->formatDate($data['ta_project_waktu_upload'] ?? null),
                'tgl_pengerjaan'  => $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null),
                'tgl_selesai'     => $this->formatDate($data['ta_project_waktu_selesai'] ?? null),
                'status'          => $data['ta_project_status'],
                'total'           => $data['ta_project_total'],
                'detail'          => $detail,
                'catatan_return' => $catatanReturn,
            ],
            'totals' => $totals,
        ]);
    }

    public function edit($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // --- Ambil detail project ---
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        $detail = [];
        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();
            $designatorData = $row['ta_detail_ta_id']->snapshot()->data();

            $hargaMaterial = $designatorData['mitra_harga_material'] ?? 0;
            $hargaJasa     = $designatorData['mitra_harga_jasa'] ?? 0;
            $volume        = $row['ta_detail_volume'] ?? 0;

            $detail[] = (object)[
                'id'             => $d->id(),
                'designator'     => $designatorData['mitra_designator'] ?? '',
                'uraian'         => $designatorData['mitra_uraian_pekerjaan'] ?? '',
                'satuan'         => $designatorData['mitra_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa'     => $hargaJasa,
                'volume'         => $volume,
                'total_material' => $hargaMaterial * $volume,
                'total_jasa'     => $hargaJasa * $volume,
            ];
        }

        $totals = $this->hitungTotal($detailDocs);

        // --- Ambil data referensi designator pakai helper ---
        [$project_mitra_doc, $uraianOptions] = $this->fetchProjectTaData();

        return view('super_admin.acc.edit_acc', [
            'acc' => [
                'id'               => $id,
                'nama_project'     => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'detail'           => $detail,
            ],
            'totals'         => $totals,
            'project_mitra_doc' => $project_mitra_doc,
        ]);
    }

    public function update(Request $request, $id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.acc')->with('error', 'Project tidak ditemukan');
        }

        // Update project name
        $docRef->update([
            ['path' => 'ta_project_pekerjaan', 'value' => $request->nama_project],
        ]);

        // Existing details
        $existingDetails = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        // Map for existing details
        $existingMap = [];
        foreach ($existingDetails as $detail) {
            $existingMap[$detail->id()] = $detail; // Using document ID as the key
        }

        // Data from the form
        $designators = $request->input('designator', []);
        $volumes = $request->input('volume', []);
        $detailIds = $request->input('detail_id', []); // Associated detail IDs

        foreach ($designators as $index => $dsg) {
            $volume = (int)($volumes[$index] ?? 0);
            $detailId = $detailIds[$index] ?? null;

            // Fetch the designator reference based on user input
            $designatorDoc = $firestore->collection('Data_Project_Mitra')->where('mitra_designator', '=', $dsg)->documents()->rows();

            if ($dsg && $volume > 0) {
                if ($detailId && isset($existingMap[$detailId])) {
                    // Update existing detail
                    $detailRef = $existingMap[$detailId];

                    // Update volume
                    $detailRef->reference()->update([
                        ['path' => 'ta_detail_volume', 'value' => $volume],
                    ]);

                    // Update designator if it has changed
                    if (count($designatorDoc) > 0) {
                        $detailRef->reference()->update([
                            ['path' => 'ta_detail_ta_id', 'value' => $designatorDoc[0]->reference()], // Save as reference
                        ]);
                    }
                } else {
                    // Add new detail if not exists
                    if (count($designatorDoc) > 0) {
                        $firestore->collection('Detail_Project_TA')->add([
                            'ta_detail_all_id' => $docRef,
                            'ta_detail_ta_id' => $designatorDoc[0]->reference(), // Save as reference
                            'ta_detail_volume' => $volume,
                        ]);
                    }
                }
            }
        }

        // Update total after changes
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();
        $totals = $this->hitungTotal($detailDocs);
        $docRef->update([['path' => 'ta_project_total', 'value' => $totals['grand']]]);

        return redirect()
            ->route('superadmin.acc_detail', $id)
            ->with('success', 'Project berhasil diperbarui');
    }

    public function destroy($id, $detailId)
    {
        $firestore = $this->getFirestore();

        // Referensi ke dokumen Detail_Project_TA yang ingin dihapus
        $detailRef = $firestore->collection('Detail_Project_TA')->document($detailId);
        $detailDoc = $detailRef->snapshot();

        if (!$detailDoc->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail tidak ditemukan.'
            ], 404);
        }

        // Hapus dokumen dari Firestore
        $detailRef->delete();

        // Hitung ulang total project setelah penghapusan
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef)
            ->documents();

        $totals = $this->hitungTotal($detailDocs);

        // Update total di dokumen induk
        $docRef->update([
            ['path' => 'ta_project_total', 'value' => $totals['grand']]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material berhasil dihapus.'
        ]);
    }

    public function destroyProject($id)
    {
        $firestore = $this->getFirestore();

        // Referensi ke dokumen project utama
        $projectRef = $firestore->collection('All_Project_TA')->document($id);
        $projectSnap = $projectRef->snapshot();

        if (!$projectSnap->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Data project tidak ditemukan.'
            ], 404);
        }

        // Ambil semua detail project yang terhubung
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $projectRef)
            ->documents();

        // Hapus semua detail project
        foreach ($detailDocs as $detail) {
            if ($detail->exists()) {
                $firestore->collection('Detail_Project_TA')->document($detail->id())->delete();
            }
        }

        // Hapus data project utama
        $projectRef->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data project dan seluruh material berhasil dihapus.'
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

        // Firestore Timestamp
        $now = new FireTimestamp(new \DateTime());

        // Update waktu pengerjaan + status
        $docRef->update([
            [
                'path' => 'ta_project_waktu_pengerjaan',
                'value' => $now,
            ],
            [
                'path' => 'ta_project_status',
                'value' => 'REKONSILIASI',
            ],
        ]);

        return redirect()->route('superadmin.acc_detail', $id)
            ->with('success', 'Project berhasil dikerjakan.');
    }

    public function storeFoto(Request $request, $id)
    {
        // dd($request->file('foto_sebelum'), $request->file('foto_sesudah'));

        try {
            $firestore = $this->getFirestore();

            $docRef = $firestore
                ->collection('acc')
                ->document($id);

            if (empty($request->file('foto_sebelum')) && empty($request->file('foto_sesudah'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada file yang diupload.'
                ], 400);
            }

            $cloudinary = new Cloudinary(config('cloudinary.url'));

            $uploaded = [
                'sebelum' => [],
                'sesudah' => [],
            ];

            // ================================
            // LOOP FOTO SEBELUM PER DESIGNATOR
            // ================================
            if (!empty($request->file('foto_sebelum'))) {
                foreach ($request->file('foto_sebelum') as $designator => $files) {
                    foreach ($files as $file) {
                        if (!$file->isValid()) continue;

                        $upload = $cloudinary->uploadApi()->upload(
                            $file->getRealPath(),
                            [
                                'folder' => "new_evident_foto/sebelum/$designator"
                            ]
                        );

                        $uploaded['sebelum'][$designator][] = $upload['secure_url'];
                    }
                }
            }

            // ================================
            // LOOP FOTO SESUDAH PER DESIGNATOR
            // ================================
            if (!empty($request->file('foto_sesudah'))) {
                foreach ($request->file('foto_sesudah') as $designator => $files) {
                    foreach ($files as $file) {
                        if (!$file->isValid()) continue;

                        $upload = $cloudinary->uploadApi()->upload(
                            $file->getRealPath(),
                            [
                                'folder' => "new_evident_foto/sesudah/$designator"
                            ]
                        );

                        $uploaded['sesudah'][$designator][] = $upload['secure_url'];
                    }
                }
            }

            // ================================
            // CARI / BUAT DOKUMEN FOTO_EVIDENT
            // ================================
            $fotoDocs = $firestore->collection('Foto_Evident')
                ->where('project_id', '=', $id)
                ->documents();

            $docRef = null;
            foreach ($fotoDocs as $d) {
                if ($d->exists()) {
                    $docRef = $firestore
                        ->collection('Foto_Evident')
                        ->document($d->id());
                    break;
                }
            }

            if (!$docRef) {
                $newDoc = $firestore->collection('Foto_Evident')->add([
                    'project_id' => $id,
                    'foto_path' => [
                        'sebelum' => [],
                        'sesudah' => [],
                    ],
                    'uploaded_at' => new FireTimestamp(new \DateTime()),
                ]);

                $docRef = $firestore
                    ->collection('Foto_Evident')
                    ->document($newDoc->id());
            }

            // ================================
            // AMBIL DATA EXISTING
            // ================================
            $snapshot = $docRef->snapshot()->data() ?? [];

            $existing = $snapshot['foto_path'] ?? [
                'sebelum' => [],
                'sesudah' => [],
            ];

            // ================================
            // CREATE / UPDATE PER DESIGNATOR
            // ================================
            foreach ($uploaded['sebelum'] as $dsg => $urls) {

                if (empty($existing['sebelum'][$dsg])) {
                    // CREATE
                    $existing['sebelum'][$dsg] = $urls;
                } else {
                    // UPDATE (replace foto lama)
                    $existing['sebelum'][$dsg] = $urls;
                }
            }

            foreach ($uploaded['sesudah'] as $dsg => $urls) {

                if (empty($existing['sesudah'][$dsg])) {
                    // CREATE
                    $existing['sesudah'][$dsg] = $urls;
                } else {
                    // UPDATE (replace foto lama)
                    $existing['sesudah'][$dsg] = $urls;
                }
            }

            // ================================
            // SIMPAN KE FIRESTORE
            // ================================
            $docRef->set([
                'project_id' => $id,
                'foto_path' => $existing,
                'uploaded_at' => new FireTimestamp(new \DateTime()),
            ], ['merge' => true]);

            // =====================================
            // UPDATE ta_project_waktu_selesai
            // =====================================
            $projectRef = $firestore
                ->collection('All_Project_TA') // pastikan nama collection benar
                ->document($id);

            $projectSnapshot = $projectRef->snapshot();

            if ($projectSnapshot->exists()) {
                $projectData = $projectSnapshot->data();

                // isi waktu selesai hanya sekali
                if (empty($projectData['ta_project_waktu_selesai'])) {
                    $projectRef->update([
                        [
                            'path'  => 'ta_project_waktu_selesai',
                            'value' => new FireTimestamp(new \DateTime()),
                        ],
                    ]);
                }

                // status selalu menjadi REVIEW TA setelah upload foto
                $projectRef->update([
                    [
                        'path'  => 'ta_project_status',
                        'value' => 'REVIEW TA',
                    ],
                ]);
            }

            return redirect()
                ->route('superadmin.acc_detail', $id)
                ->with('success', 'Foto evident berhasil diupload.');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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

    public function returnProject(Request $request, $id)
    {
        $request->validate([
            'catatan_return' => 'required|string'
        ]);

        $firestore = $this->getFirestore();

        // simpan catatan return
        $firestore->collection('Return_Project')->add([
            'project_id' => $id,
            'catatan' => $request->catatan_return,
            'created_at' => new FireTimestamp(new \DateTime())
        ]);

        // ubah status project
        $firestore->collection('All_Project_TA')
            ->document($id)
            ->update([
                [
                    'path' => 'ta_project_status',
                    'value' => 'REKONSILIASI'
                ]
            ]);

        return redirect()
            ->route('superadmin.acc_detail', $id)
            ->with('success', 'Project berhasil direturn.');
    }

    public function closeProject($id)
    {
        $firestore = $this->getFirestore();

        $firestore->collection('All_Project_TA')
            ->document($id)
            ->update([
                [
                    'path' => 'ta_project_status',
                    'value' => 'CLOSE'
                ]
            ]);

        return redirect()
            ->route('superadmin.acc_detail', $id)
            ->with('success', 'Project berhasil di-close.');
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
