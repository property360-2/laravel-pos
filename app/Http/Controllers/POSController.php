<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class POSController extends Controller
{
    public function index(): View
    {
        return view('pos.index');
    }
}
