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

    public function index(Request $request)
    {
        $foto_doc    = $this->fetchFotoData();
        $pending_doc = $this->fetchPendingData();
        $qe_doc      = $this->fetchQEData();
        [$process_doc, $grandTotal] = $this->fetchProcessData($request);

        return view('super_admin.process.process_superadmin', compact('process_doc', 'grandTotal'));
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

    private function fetchProcessData($request)
    {
        $process_collection = $this->getFirestore()->collection('All_Project_TA')->documents();
        $process_doc = [];
        $tot = 0;

        // Ambil filter dari query string
        $startDate = $request->query('start');
        $endDate   = $request->query('end');

        foreach ($process_collection as $docp) {
            if ($docp->exists()) {
                $data = $docp->data();

                if (($data['ta_project_status'] ?? '') !== 'PROCESS') {
                    continue;
                }

                // Ambil tanggal upload
                $tglUploadRaw = $data['ta_project_waktu_upload'] ?? null;
                $tglUpload    = $this->formatDate($tglUploadRaw);

                // Filter kalau ada query tanggal
                if ($startDate && $endDate) {
                    if ($tglUpload < $startDate || $tglUpload > $endDate) {
                        continue; // skip data di luar range
                    }
                }

                $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
                $tglSelesai    = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);
                $totalValue    = (float) ($data['ta_project_total'] ?? 0);

                $qeData = $this->getReferenceData($data['ta_project_qe_id']);

                $process_doc[] = [
                    'id'               => $docp->id(),
                    'nama_project'     => $data['ta_project_pekerjaan'],
                    'deskripsi_project' => $data['ta_project_deskripsi'],
                    'qe'               => $qeData ? $qeData['type'] : null,
                    'tgl_upload'       => $tglUpload,
                    'tgl_pengerjaan'   => $tglPengerjaan,
                    'tgl_selesai'      => $tglSelesai,
                    'status'           => $data['ta_project_status'],
                    'total'            => number_format($totalValue, 0, ',', '.'),
                ];

                $tot += $totalValue;
            }
        }

        return [$process_doc, number_format($tot, 0, ',', '.')];
    }

    private function getReferenceData($ref)
    {
        if ($ref) {
            $doc = $ref->snapshot();
            return $doc->exists() ? $doc->data() : null;
        }
        return null;
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

    public function detail($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);
        $doc = $docRef->snapshot();

        if (!$doc->exists()) {
            return redirect()->route('superadmin.process')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

        // --- Ambil data project utama ---
        $fotoData    = $data['ta_project_foto_id']    ? $data['ta_project_foto_id']->snapshot()->data()    : null;
        $pendingData = $data['ta_project_pending_id'] ? $data['ta_project_pending_id']->snapshot()->data() : null;
        $qeData      = $data['ta_project_qe_id']      ? $data['ta_project_qe_id']->snapshot()->data()      : null;

        $tglUpload     = $this->formatDate($data['ta_project_waktu_upload'] ?? null);
        $tglPengerjaan = $this->formatDate($data['ta_project_waktu_pengerjaan'] ?? null);
        $tglSelesai    = $this->formatDate($data['ta_project_waktu_selesai'] ?? null);

        // --- Ambil detail ---
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

        return view('super_admin.process.detail_process', [
            'process' => [
                'id'               => $id,
                'nama_project'     => $data['ta_project_pekerjaan'],
                'deskripsi_project' => $data['ta_project_deskripsi'],
                'qe'               => $qeData['type'] ?? null,
                'foto'             => $fotoData,
                'pending'          => $pendingData,
                'tgl_upload'       => $tglUpload,
                'tgl_pengerjaan'   => $tglPengerjaan,
                'tgl_selesai'      => $tglSelesai,
                'status'           => $data['ta_project_status'],
                'total'            => $data['ta_project_total'],
                'detail'           => $detail,
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
            return redirect()->route('superadmin.process')->with('error', 'Data project tidak ditemukan');
        }

        $data = $doc->data();

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

        // Ambil data referensi designator buat dropdown
        $project_ta_doc = [];
        $project_ta_collection = $firestore->collection('TA')->documents();
        foreach ($project_ta_collection as $ta) {
            if ($ta->exists()) {
                $project_ta_doc[] = [
                    'id'            => $ta->id(),
                    'designator'    => $ta->data()['ta_designator'] ?? '',
                    'uraian'        => $ta->data()['ta_uraian_pekerjaan'] ?? '',
                    'satuan'        => $ta->data()['ta_satuan'] ?? '',
                    'harga_material' => $ta->data()['ta_harga_material'] ?? 0,
                    'harga_jasa'    => $ta->data()['ta_harga_jasa'] ?? 0,
                ];
            }
        }

        return view('super_admin.process.edit_process', [
            'process' => [
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
            return redirect()->route('superadmin.process')->with('error', 'Project tidak ditemukan');
        }

        // update nama project
        $docRef->update([
            ['path' => 'ta_project_pekerjaan', 'value' => $request->nama_project],
        ]);

        // update detail
        $designators = $request->input('designator', []);
        $volumes     = $request->input('volume', []);
        $detailCollection = $firestore->collection('Detail_Project_TA');

        foreach ($designators as $index => $dsgId) {
            $volume = (int)($volumes[$index] ?? 0);
            if ($dsgId && $volume > 0) {
                $designatorRef = $firestore->collection('Data_Project_TA')->document($dsgId);

                // cek apakah sudah ada detail yg sama
                $existing = $detailCollection
                    ->where('ta_detail_all_id', '=', $docRef)
                    ->where('ta_detail_ta_id', '=', $designatorRef)
                    ->documents();

                if (count(iterator_to_array($existing)) > 0) {
                    foreach ($existing as $exist) {
                        $detailCollection->document($exist->id())->update([
                            ['path' => 'ta_detail_volume', 'value' => $volume],
                        ]);
                    }
                } else {
                    $detailCollection->add([
                        'ta_detail_all_id' => $docRef,
                        'ta_detail_ta_id'  => $designatorRef,
                        'ta_detail_volume' => $volume,
                    ]);
                }
            }
        }

        $detailDocs = $detailCollection->where('ta_detail_all_id', '=', $docRef)->documents();
        $totals = $this->hitungTotal($detailDocs);

        $docRef->update([['path' => 'ta_project_total', 'value' => $totals['grand']]]);

        return redirect()
            ->route('superadmin.process_detail', $id)
            ->with('success', 'Project berhasil diperbarui');
    }

    public function destroy($id)
    {
        $firestore = $this->getFirestore();

        try {
            // hapus dokumen berdasarkan ID di Detail_Project_TA
            $firestore->collection('Detail_Project_TA')->document($id)->delete();

            return redirect()->back()->with('success', 'Material berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus material: ' . $e->getMessage());
        }
    }

    public function acc($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);

        $doc = $docRef->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('superadmin.process')->with('error', 'Project tidak ditemukan');
        }

        // Update status jadi ACC
        $docRef->update([
            ['path' => 'ta_project_status', 'value' => 'ACC'],
        ]);

        return redirect()->route('superadmin.acc')->with('success', 'Project berhasil di-ACC');
    }

    public function reject($id)
    {
        $firestore = $this->getFirestore();
        $docRef = $firestore->collection('All_Project_TA')->document($id);

        $doc = $docRef->snapshot();
        if (!$doc->exists()) {
            return redirect()->route('superadmin.process')->with('error', 'Project tidak ditemukan');
        }

        // Update status jadi REJECT
        $docRef->update([
            ['path' => 'ta_project_status', 'value' => 'REJECT'],
        ]);

        return redirect()->route('superadmin.reject')->with('success', 'Project berhasil di-Reject');
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
