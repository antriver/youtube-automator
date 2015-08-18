<?php

namespace YoutubeAutomator\Http\Controllers;

use Auth;
use Google_Client;
use Redirect;
use Illuminate\Http\Request;

class RootController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getIndex(Request $request, Google_Client $googleClient)
    {
        $user = Auth::user();
        return view('index', [
            'user' => $user
        ]);
    }
}
