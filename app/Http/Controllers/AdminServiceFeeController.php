<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminServiceFeeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('admin/ServiceFeeSanksi');
    }
}
