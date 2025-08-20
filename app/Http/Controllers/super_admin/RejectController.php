<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class RejectController extends Controller
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
        $firestore = $this->getFirestore();
        $projectsRef = $firestore->collection('projects')
                                 ->where('status', '=', 'Reject')
                                 ->documents();

        $rejectProjects = [];
        $totalProject = 0;

        foreach ($projectsRef as $doc) {
            if ($doc->exists()) {
                $rejectProjects[] = array_merge($doc->data(), ['id' => $doc->id()]);
                $totalProject++;
            }
        }

        return view('super_admin.reject.reject_superadmin', compact('rejectProjects', 'totalProject'));
    }
}
