<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;

class AuthController extends Controller
{
    /**
     * Ambil instance Firestore.
     * Tidak disimpan di property agar tidak menimbulkan recursive serialization.
     */
    private function getFirestore()
    {
        return new FirestoreClient([
            'projectId' => 'luwina-381dd',
            'keyFilePath' => config('firebase.credentials')
        ]);
    }

    public function index()
    {
        return view('auth.login');
    }

    public function proses_login(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'password' => 'required'
        ]);

        // Ambil Firestore instance hanya di sini
        $firestore = $this->getFirestore();
        $usersRef = $firestore->collection('User');
        $query = $usersRef->where('user_nik', '=', $request->nik)->documents();

        if ($query->isEmpty()) {
            return back()->with('failed', 'Username atau Password Salah');
        }

        foreach ($query as $userDoc) {
            $userData = $userDoc->data();

            // Cocokkan password (gunakan hashing di produksi)
            if ($userData['user_password'] === $request->password) {
                // Simpan data penting saja di session (jangan object Firestore)
                session([
                    'user_id'   => $userDoc->id(),
                    'user_nama' => $userData['user_nama'],
                    'user_role' => $userData['user_role'],
                ]);

                return redirect()->route('welcome');
            }
        }

        return back()->with('failed', 'Username atau Password Salah');
    }

    public function logout(Request $request)
    {
        session()->flush();
        return redirect()->route('login');
    }
}
