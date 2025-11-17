<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    //demo functio
    public function show(){
        return view('hello');
    }
}
