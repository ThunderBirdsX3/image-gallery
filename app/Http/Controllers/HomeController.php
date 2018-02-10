<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all galleries by user, Then make it to collection.
        $gallery = collect(Auth::user()->Gallery()
        ->select('file_type', 'file_size')
        ->get()->toArray());

        return view('home', compact('gallery'));
    }
}
