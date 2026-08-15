<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'settings' => Setting::allAsArray(),
        ]);
    }
}
