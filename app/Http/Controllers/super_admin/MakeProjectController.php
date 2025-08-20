<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class MakeProjectController extends Controller
{
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')), // Ambil dari .env
        ]);
    }

    public function index()
    {
        // GET QE
        $qeCollection = $this->getFirestore()->collection('QE')->documents();

        $qeOptions = [];
        foreach ($qeCollection as $docq) {
            if ($docq->exists()) {
                $qeOptions[] = [
                    'id' => $docq->id(),               // document ID (1, 2, 3, 4)
                    'label' => $docq->data()['type'], // field type (PREVENTIVE, dll.)
                ];
            }
        }

        // Urutkan berdasarkan ID (optional)
        usort($qeOptions, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);

        // GET DESIGNATOR
        $project_ta_collection = $this->getFirestore()->collection('Data_Project_TA')->documents();

        $project_ta_doc = [];
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
            }
        }

        // Urutkan berdasarkan ID (optional)
        usort($project_ta_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);

        return view('super_admin.makeproject.makeproject_superadmin', compact('qeOptions'), compact('project_ta_doc'));
    }

    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     'qe' => 'required|string|max:255',
        //     'pekerjaan' => 'required|string|max:255',
        //     'deskripsi' => 'required|string|max:255',
        //     'nomor_khs' => 'required|string|max:255',
        //     'pelaksana' => 'required|string|max:255',
        //     'wilayah' => 'required|string|max:255',
        // ]);

        // return redirect()->route('project.index')->with('success', 'Project created successfully!');
    }
}
