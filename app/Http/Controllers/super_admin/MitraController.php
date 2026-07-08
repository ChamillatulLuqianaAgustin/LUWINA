<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class MitraController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/firebase/luwina-ta-firebase-adminsdk-fbsvc-e165a7c4f0.json'),
        ]);
    }

    public function index()
    {
        $mitra_doc = $this->fetchMitraData();

        return view('super_admin.mitra.mitra_superadmin', compact('mitra_doc'));
    }

    private function fetchMitraData()
    {
        $mitra_collection = $this->getFirestore()
            ->collection('Unit_Kerja')
            ->documents();

        $mitra_doc = [];

        foreach ($mitra_collection as $doc) {

            if ($doc->exists()) {

                $data = $doc->data();

                // hanya ambil yang Jenis = Mitra
                if (($data['Jenis'] ?? '') == 'Mitra') {

                    $mitra_doc[] = [
                        'id' => $doc->id(),
                        'unit' => $data['Unit'],
                        'jenis' => $data['Jenis'],
                    ];
                }
            }
        }

        usort($mitra_doc, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);

        return $mitra_doc;
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit' => 'required|string'
        ]);

        $firestore = $this->getFirestore();

        // Ambil ID terbesar
        $documents = $firestore->collection('Unit_Kerja')->documents();

        $maxId = 0;

        foreach ($documents as $doc) {
            $id = (int)$doc->id();

            if ($id > $maxId) {
                $maxId = $id;
            }
        }

        $newId = (string)($maxId + 1);

        $firestore->collection('Unit_Kerja')
            ->document($newId)
            ->set([
                'Unit' => strtoupper($request->unit),
                'Jenis' => 'Mitra'
            ]);

        return redirect()->back()
            ->with('success', 'Mitra berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'unit' => 'required|string'
        ]);

        $this->getFirestore()
            ->collection('Unit_Kerja')
            ->document($id)
            ->update([
                [
                    'path' => 'Unit',
                    'value' => strtoupper($request->unit)
                ]
            ]);

        return redirect()->back()
            ->with('success', 'Mitra berhasil diperbarui');
    }

    public function destroy($id)
    {
        $this->getFirestore()
            ->collection('Unit_Kerja')
            ->document($id)
            ->delete();

        return redirect()->back()
            ->with('success', 'Mitra berhasil dihapus');
    }
}
