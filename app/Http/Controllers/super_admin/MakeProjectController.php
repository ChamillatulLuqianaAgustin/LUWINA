<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Cache;

class MakeProjectController extends Controller
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
        $qeOptions = $this->fetchQEOptions();
        [$project_ta_doc, $uraianOptions] = $this->fetchProjectTaData();

        return view('super_admin.makeproject.makeproject_superadmin', compact('qeOptions', 'project_ta_doc', 'uraianOptions'));
    }

    private function fetchQEOptions()
    {
        return Cache::remember('qe_options', 3600, function () {
            $qeCollection = $this->getFirestore()->collection('QE')->documents();
            $data = [];
            foreach ($qeCollection as $docq) {
                if ($docq->exists()) {
                    $data[] = [
                        'id' => $docq->id(),
                        'label' => $docq->data()['type'],
                    ];
                }
            }
            usort($data, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);
            return $data;
        });
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

    public function store(Request $request)
    {
        // // Validate the incoming request data
        // $validatedData = $request->validate([
        //     'qe' => 'required|string|max:255',
        //     'pekerjaan' => 'required|string|max:255',
        //     'deskripsi' => 'required|string|max:255',
        //     'nomor_khs' => 'required|string|max:255',
        //     'pelaksana' => 'required|string|max:255',
        //     'wilayah' => 'required|string|max:255',
        // ]);

        // // Logic to store the project in Firestore would go here

        // return redirect()->route('project.index')->with('success', 'Project created successfully!');
    }
}
