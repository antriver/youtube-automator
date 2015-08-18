<?php

namespace YouTubeAutomator\Models\YouTube;

use App;
use Google_Service_YouTube;

class Video
{
    public $videoId;
    private $isHydrated = false;
    private $data;

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
        $this->hydrate();
    }

    public function getTitle()
    {
        return $this->data->snippet->title;
    }

    public function getThumbnail()
    {
        return $this->data->snippet->thumbnails->default->url;
    }

    public function isPublished()
    {
        return $this->data->status->privacyStatus === 'public';
    }

    private function hydrate()
    {
        $googleClient = App::make('Google_Client');
        $youtube = new Google_Service_YouTube($googleClient);

        $response = $youtube->videos->listVideos(
            'snippet,status',
            array('id' => $this->videoId)
        );

        $this->data = $response->items[0];

        $this->isHydrated = true;
    }
}
