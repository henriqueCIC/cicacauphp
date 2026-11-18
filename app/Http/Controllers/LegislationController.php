<?php

namespace App\Http\Controllers;

use App\Models\Legislation;

class LegislationController extends Controller
{
    public function index()
    {
        $legislations = Legislation::published()
            ->with('user')
            ->paginate(12);

        return view('legislacao', compact('legislations'));
    }
}
