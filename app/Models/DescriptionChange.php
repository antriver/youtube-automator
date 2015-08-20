<?php

namespace YouTubeAutomator\Models;

use App;
use DateTime;
use Google_Service_YouTube;
use Illuminate\Database\Eloquent\Model;
use YouTubeAutomator\Models\YouTube\Video;

class DescriptionChange extends Model
{
    public $guarded = [];

    /**
     * Return the video model for this change.
     *
     * @return Video
     */
    private function getVideo()
    {
        return new Video($this->video_id);
    }

    /**
     * Change the video's description via the YouTube API.
     *
     * @return boolean
     */
    public function execute()
    {
        $video = $this->getVideo();

        $videoData = $video->getData();
        $videoData->snippet->description = $this->description;

        $googleClient = App::make('Google_Client');
        $youtube = new Google_Service_YouTube($googleClient);

        // Update the video resource by calling the videos.update() method.
        $youtube->videos->update("snippet", $video);

        $video->fresh();

        $this->success = ($video->getDescription() == $this->description);

        $this->executed_at = (new DateTime)->format('Y-m-d H:i:s');
        $this->save();

        return $this->success;
    }
}
