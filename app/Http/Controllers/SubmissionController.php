<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function create()
        {
            return view('submissions.create');
        }
}
