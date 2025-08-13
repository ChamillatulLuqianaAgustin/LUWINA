<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        return view('super_admin.process_superadmin');
    }
}
