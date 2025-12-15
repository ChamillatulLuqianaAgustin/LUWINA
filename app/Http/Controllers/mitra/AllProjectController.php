<?php

namespace App\Http\Controllers\mitra;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Imports\TaImport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

class AllProjectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
        ]);
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

    private function fetchAllProjects($start = null, $end = null)
    {
        $project_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $project_doc = [];
        $tot = 0;

        foreach ($project_collection as $docp) {
            if ($docp->exists()) {
                $data = $docp->data();

                // Ambil timestamp mentah
                $rawUpload = $data['ta_project_waktu_upload'] ?? null;
                $rawPengerjaan = $data['ta_project_waktu_pengerjaan'] ?? null;
                $rawSelesai = $data['ta_project_waktu_selesai'] ?? null;

                // Konversi ke string tanggal untuk tampilan
                $tglUpload = $this->formatDate($rawUpload);
                $tglPengerjaan = $this->formatDate($rawPengerjaan);
                $tglSelesai = $this->formatDate($rawSelesai);

                $totalValue = (float) ($data['ta_project_total'] ?? 0);

                // 🚨 FILTER disini (gunakan rawUpload langsung)
                if ($start && $end && $rawUpload) {
                    if (method_exists($rawUpload, 'get')) {
                        $uploadDate = Carbon::instance($rawUpload->get());
                    } else {
                        $uploadDate = Carbon::parse($rawUpload);
                    }

                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate   = Carbon::parse($end)->endOfDay();

                    if (!($uploadDate->between($startDate, $endDate))) {
                        continue; // skip kalau di luar range
                    }
                }

                $projectQERef = $data['ta_project_qe_id'];
                $qeData = $this->getReferenceData($projectQERef);

                $project_doc[] = [
                    'id' => $docp->id(),
                    'nama_project' => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe' => $qeData ? $qeData['type'] : null,
                    'tgl_upload' => $tglUpload,
                    'tgl_pengerjaan' => $tglPengerjaan,
                    'tgl_selesai' => $tglSelesai,
                    'status' => $data['ta_project_status'],
                    'total' => $totalValue,
                    'total_formatted' => number_format($totalValue, 0, ',', '.'),
                ];

                $tot += $totalValue;
            }
        }

        return [$project_doc, number_format($tot, 0, ',', '.')];
    }

    private function fetchProjectTaData()
    {
        return Cache::remember('project_ta_doc', 3600, function () {
            $project_ta_collection = $this->getFirestore()->collection('Data_Project_TA')->documents();

            $project_ta_doc = [];
            $uraianOptions = [];
            foreach ($project_ta_collection as $docd) {
                if ($docd->exists()) {
                    $project_ta_doc[] = [
                        'id' => $docd->id(),
                        'designator' => $docd->data()['ta_designator'],
                        'uraian' => $docd->data()['ta_uraian_pekerjaan'],
                        'satuan' => $docd->data()['ta_satuan'],
                        'harga_material' => $docd->data()['ta_harga_material'],
                        'harga_jasa' => $docd->data()['ta_harga_jasa'],
                    ];
                    $uraianOptions[] = $docd->data()['ta_uraian_pekerjaan'];
                }
            }

            $uraianOptions = array_values(array_unique($uraianOptions));
            sort($uraianOptions);
            usort($project_ta_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);

            return [$project_ta_doc, $uraianOptions];
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

    public function index(Request $request)
    {
        // Fetch data using separate functions
        $foto_doc = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc = $this->fetchQEData();

        // ambil query param dari URL (?start=...&end=...)
        $start = $request->query('start');
        $end   = $request->query('end');

        list($project_doc, $grandTotal) = $this->fetchAllProjects($start, $end);

        // Prepare data for charts
        $totalProject = count($project_doc);
        $totalRevenue = array_sum(array_column($project_doc, 'total'));

        // Data per month for the current year
        $dataPerBulan = array_fill(1, 12, 0);
        foreach ($project_doc as $project) {
            if (!empty($project['tgl_upload'])) {
                $bulan = (int) date('n', strtotime($project['tgl_upload']));
                $tahun = (int) date('Y', strtotime($project['tgl_upload']));
                if ($tahun == date('Y')) {
                    $dataPerBulan[$bulan]++;
                }
            }
        }
        $chartTotalProjectData = array_values($dataPerBulan);

        // Chart data for QE
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

        // Chart data for project status
        $chartPieData = [];
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

        return view('mitra.allproject.allproject_mitra', compact(
            'project_doc',
            'grandTotal',
            'chartTotalProjectData',
            'chartQEData',
            'chartPieData',
            'qe_doc'
        ));
    }

    private function hitungTotal($detailDocs)
    {
        $totalMaterial = 0;
        $totalJasa     = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();
            $designatorData = $row['ta_detail_ta_id']->snapshot()->data();
            $volume         = $row['ta_detail_volume'] ?? 0;

            $totalMaterial += ($designatorData['ta_harga_material'] ?? 0) * $volume;
            $totalJasa     += ($designatorData['ta_harga_jasa'] ?? 0) * $volume;
        }

        $total = $totalMaterial + $totalJasa;
        $ppn   = $total * 0.11;
        $grand = $total + $ppn;

        return [
            'material' => $totalMaterial,
            'jasa'     => $totalJasa,
            'total'    => $total,
            'ppn'      => $ppn,
            'grand'    => $grand,
        ];
    }

    public function create(Request $request)
    {
        try {
            $firestore = $this->getFirestore();

            // 🔒 Validasi input
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
                'qe' => 'required|string',
                'status' => 'required|string',
                'deskripsi' => 'required|string',
            ]);

            // 1️⃣ Jalankan import Excel
            $rows = Excel::toCollection(new TAImport, $request->file('file'))[0];
            $import = new TAImport();
            $import->collection($rows);

            // dd([
            //     'headerData' => $import->headerData,
            //     'detailData' => $import->detailData,
            // ]);

            $header = $import->headerData;
            $details = $import->detailData;

            // 2️⃣ Ambil reference QE dari Firestore
            $qe_collection = $firestore->collection('QE');
            $qe_doc = $qe_collection->where('type', '=', $request->qe)->documents();

            $qe_ref = null;
            foreach ($qe_doc as $doc) {
                if ($doc->exists()) {
                    $qe_ref = $qe_collection->document($doc->id());
                    break;
                }
            }

            if (!$qe_ref) {
                return back()->with('error', 'QE tidak ditemukan.');
            }

            // 3️⃣ Simpan ke All_Project_TA
            $allProjectRef = $firestore->collection('All_Project_TA')->add([
                'ta_project_qe_id'        => $qe_ref,
                'ta_project_pekerjaan'    => $header['ta_project_pekerjaan'],
                'ta_project_deskripsi'    => $request->deskripsi,
                'ta_project_khs'          => $header['ta_project_khs'],
                'ta_project_pelaksana'    => $header['ta_project_pelaksana'],
                'ta_project_witel'        => $header['ta_project_witel'],
                'ta_project_foto_id'      => null,
                'ta_project_pending_id'   => null,
                'ta_project_status'       => $request->status,
                'ta_project_total'        => 0,
                'ta_project_waktu_pengerjaan' => null,
                'ta_project_waktu_selesai'    => null,
                'ta_project_waktu_upload'     => Carbon::now(),
            ]);

            // 4️⃣ Simpan ke Detail_Project_TA (hanya baris valid sesuai TAImport)
            $dataProjectCollection = $firestore->collection('Data_Project_TA');
            foreach ($details as $detail) {
                $designator = $detail['designator'];
                $volume = $detail['volume'];

                // 🔍 Cari dokumen designator di Data_Project_TA
                $dataTA = $dataProjectCollection->where('ta_designator', '=', $designator)->documents();
                $dataRef = null;
                foreach ($dataTA as $d) {
                    if ($d->exists()) {
                        $dataRef = $dataProjectCollection->document($d->id());
                        break;
                    }
                }

                // Lewati kalau tidak ditemukan
                if (!$dataRef) continue;

                // 🔹 Tambahkan ke Detail_Project_TA
                $firestore->collection('Detail_Project_TA')->add([
                    'ta_detail_all_id' => $allProjectRef,
                    'ta_detail_ta_id'  => $dataRef,
                    'ta_detail_volume' => $volume,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data project berhasil diupload!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('mitra.allproject')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // Fetch related data
        $fotoData = $this->getReferenceData($data['ta_project_foto_id'] ?? null);
        $pendingData = $this->getReferenceData($data['ta_project_pending_id'] ?? null);
        $qeData = $this->getReferenceData($data['ta_project_qe_id'] ?? null);

        $tglUpload = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        // Fetch detail data from Detail_Project_TA
        $detailDocs = $firestore->collection('Detail_Project_TA')
            ->where('ta_detail_all_id', '=', $docRef) // filter by project reference
            ->documents();

        $detail = [];
        $totalMaterial = 0;
        $totalJasa = 0;

        foreach ($detailDocs as $d) {
            if (!$d->exists()) continue;

            $row = $d->data();

            // Fetch data from Data_Project_TA
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
                'id' => $d->id(),
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
        $grand = $total + $ppn;

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

        return view('mitra.allproject.detail_allproject', [
            'allproject' => [
                'id' => $id,
                'nama_project' => $data['ta_project_pekerjaan'],
                // 'deskripsi_project' => $data['ta_project_deskripsi'],
                // 'qe' => $qeData['type'] ?? null,
                'foto' => $fotoData,
                'pending' => $pendingData,
                // 'tgl_upload' => $tglUpload,
                // 'tgl_pengerjaan' => $tglPengerjaan,
                // 'tgl_selesai' => $tglSelesai,
                // 'status' => $data['ta_project_status'],
                'total' => $data['ta_project_total'],
                'detail' => $detail,
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
            return redirect()->route('mitra.allproject')->with('error', 'Data project tidak ditemukan');
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

            $hargaMaterial = $designatorData['ta_harga_material'] ?? 0;
            $hargaJasa     = $designatorData['ta_harga_jasa'] ?? 0;
            $volume        = $row['ta_detail_volume'] ?? 0;

            $detail[] = (object)[
                'id'             => $d->id(),
                'designator'     => $designatorData['ta_designator'] ?? '',
                'uraian'         => $designatorData['ta_uraian_pekerjaan'] ?? '',
                'satuan'         => $designatorData['ta_satuan'] ?? '',
                'harga_material' => $hargaMaterial,
                'harga_jasa'     => $hargaJasa,
                'volume'         => $volume,
                'total_material' => $hargaMaterial * $volume,
                'total_jasa'     => $hargaJasa * $volume,
            ];
        }

        $totals = $this->hitungTotal($detailDocs);

        // --- Ambil data referensi designator pakai helper ---
        [$project_ta_doc, $uraianOptions] = $this->fetchProjectTaData();

        return view('mitra.allproject.edit_allproject', [
            'allproject' => [
                'id'               => $id,
                'nama_project'     => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'detail'           => $detail,
            ],
            'totals'         => $totals,
            'project_ta_doc' => $project_ta_doc,
        ]);
    }

    public function update(Request $request, $id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('mitra.allproject')->with('error', 'Project tidak ditemukan');
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
            $designatorDoc = $firestore->collection('Data_Project_TA')->where('ta_designator', '=', $dsg)->documents()->rows();

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
            ->route('mitra.allproject_detail', $id)
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

    public function downloadPDF(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        list($project_doc, $grandTotal) = $this->fetchAllProjects($start, $end);

        // 🔧 Gunakan format parser yang konsisten
        if ($start && $end) {
            $startFormatted = Carbon::createFromFormat('Y-m-d', $start)->translatedFormat('j M Y');
            $endFormatted   = Carbon::createFromFormat('Y-m-d', $end)->translatedFormat('j M Y');
            $title = "All Project TA ({$startFormatted} - {$endFormatted})";
        } else {
            $title = "All Project TA - " . now()->translatedFormat('j M Y');
        }

        $pdf = Pdf::loadView('mitra.allproject.download_mitra', [
            'project_doc' => $project_doc,
            'grandTotal' => $grandTotal,
            'title' => $title,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('All_Project_' . now()->format('Y-m-d') . '.pdf');
    }
}
