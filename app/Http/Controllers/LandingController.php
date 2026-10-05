<?php

namespace App\Http\Controllers;

use App\Models\BarangayInformation;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing', [
            'information' => BarangayInformation::query()->first(),
        ]);
    }
}
