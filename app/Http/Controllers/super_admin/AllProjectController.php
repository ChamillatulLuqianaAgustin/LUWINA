<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;

class AllProjectController extends Controller
{
    public function dashboard()
    {
        return view('super_admin.allproject_superadmin');
    }
}
