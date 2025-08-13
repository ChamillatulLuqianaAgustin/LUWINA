<?php

namespace App\Http\Controllers\super_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RejectController extends Controller
{
    public function index()
    {
        return view('super_admin.reject_superadmin');
    }
}
