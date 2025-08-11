<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('super_admin.dashboard_superadmin');
    }
}
