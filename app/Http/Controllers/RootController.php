<?php

namespace YouTubeAutomator\Http\Controllers;

use Auth;
use Config;
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

    /**
     * GET /
     * Display the homepage.
     */
    public function getIndex()
    {
        return redirect()->secure('/videos');
    }

    /**
     * GET /videos
     * Display the list of users videos.
     *
     * @param  Google_Client $googleClient
     * @return \Illuminate\View\View
     */
    public function getVideos(Google_Client $googleClient)
    {
        $videos = [];

        $youtube = new Google_Service_YouTube($googleClient);
        $searchResponse = $youtube->search->listSearch('id', [
            'q' => '*',
            'type' => 'video',
            'forMine' => 1,
            'maxResults' => Config::get('app.videos_per_page')
        ]);

        foreach ($searchResponse->items as $item) {
            $videos[] = new Video($item->id->videoId);
        }

        return view('videos', [
            'videos' => $videos
        ]);
    }
}
