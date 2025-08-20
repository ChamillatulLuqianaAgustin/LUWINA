<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Google\Cloud\Firestore\FirestoreClient;

class AllProjectController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $this->firestore = new FirestoreClient([
            'projectId' => env('FIREBASE_PROJECT_ID'),
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
        ]);
    }

    public function allproject()
    {
        $projectsRef = $this->firestore->collection('projects');
        $documents = $projectsRef->documents();

        $projects = collect();
        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();

                // Perbaikan format tanggal_upload
                if (isset($data['tanggal_upload']) && is_object($data['tanggal_upload'])) {
                    if (method_exists($data['tanggal_upload'], 'toDateTime')) {
                        $data['tanggal_upload'] = $data['tanggal_upload']->toDateTime()->format('Y-m-d');
                    } elseif (isset($data['tanggal_upload']->seconds)) {
                        $data['tanggal_upload'] = date('Y-m-d', $data['tanggal_upload']->seconds);
                    }
                }

                $projects->push((object) $data);
            }
        }

        $totalProject = $projects->count();
        $totalRevenue = $projects->sum(fn($item) => $item->revenue ?? 0);

        // Distribusi total project tahun 2025
        $dataPerBulan = array_fill(1, 12, 0);
        foreach ($projects as $project) {
            if (!empty($project->tanggal_upload)) {
                $bulan = (int) date('n', strtotime($project->tanggal_upload));
                $tahun = (int) date('Y', strtotime($project->tanggal_upload));

                if ($tahun == 2025) {
                    $dataPerBulan[$bulan]++;
                }
            }
        }
        $chartTotalProjectData = array_values($dataPerBulan);

        $chartTodayData = [
            'PROCESS' => 9,
            'ACC' => 7,
            'REJECT' => 1,
        ];

        $chartPieData = [
            'PROCESS' => 50,
            'ACC' => 40,
            'REJECT' => 10,
        ];

        return view('super_admin.allproject.allproject_superadmin', compact(
            'projects',
            'totalProject',
            'totalRevenue',
            'chartTotalProjectData',
            'chartTodayData',
            'chartPieData'
        ));
    }
}
