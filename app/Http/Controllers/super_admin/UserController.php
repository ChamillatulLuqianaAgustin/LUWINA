<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class UserController extends Controller
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
        // GET USER
        $usr_collection = $this->getFirestore()->collection('User')->documents();

        $usr_doc = [];
        foreach ($usr_collection as $docu) {
            if ($docu->exists()) {
                $data = $docu->data();
                $userRoleRef = $data['user_role']; // Ambil referensi user_role

                // Ambil data dari koleksi Role jika user_role adalah referensi
                $roleData = null;
                if ($userRoleRef) {
                    $roleDoc = $userRoleRef->snapshot(); // Ambil snapshot dari referensi
                    if ($roleDoc->exists()) {
                        $roleData = $roleDoc->data();
                    }
                }

                $usr_doc[] = [
                    'id' => $docu->id(),
                    'nik' => $data['user_nik'],
                    'nama' => $data['user_nama'], // Perbaiki key dari 'nik' menjadi 'nama'
                    'uker' => $data['user_sto'],
                    'password' => $data['user_password'],
                    'role' => $roleData ? $roleData['role'] : null, // Ambil nama role dari field 'role'
                ];
            }
        }

        // Urutkan berdasarkan ID (optional)
        usort($usr_doc, fn($a, $b) => (int)$a['id'] <=> (int)$b['id']);

        // GET ROLE
        $role_collection = $this->getFirestore()->collection('Role')->documents();

        $role_doc = [];
        foreach ($role_collection as $docr) {
            if ($docr->exists()) {
                $role_doc[] = [
                    'id' => $docr->id(),
                    'role' => $docr->data()['role'],
                ];
            }
        }

        // Urutkan berdasarkan ID (optional)
        usort($role_doc, fn($c, $d) => (int)$c['id'] <=> (int)$d['id']);

        return view('super_admin.user_superadmin', compact('usr_doc'), compact('role_doc')); // Kirim data ke view
    }
}
