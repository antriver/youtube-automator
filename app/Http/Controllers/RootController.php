<?php

namespace YouTubeAutomator\Http\Controllers;

use Auth;
use Google_Client;
use Google_Service_YouTube;
use Redirect;
use Illuminate\Http\Request;
use YouTubeAutomator\Models\YouTube\Video;

class RootController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getIndex()
    {
        return redirect()->secure('/videos');
    }

    public function getVideos(Google_Client $googleClient)
    {
        $videos = [];

        $youtube = new Google_Service_YouTube($googleClient);
        $searchResponse = $youtube->search->listSearch('id', [
            'q' => '*',
            'type' => 'video',
            'forMine' => 1,
            'maxResults' => 3
        ]);

        foreach ($searchResponse->items as $item) {
            $videos[] = new Video($item->id->videoId);
        }

        return view('videos', [
            'videos' => $videos
        ]);
    }
}
