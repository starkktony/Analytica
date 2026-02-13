<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return view('student');
    }

    public function enrollment()
    {
        return view('student.enrollment');
    }
}
