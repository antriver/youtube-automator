<?php

namespace YouTubeAutomator\Models;

use App;
use DateTime;
use Google_Service_YouTube;
use Illuminate\Database\Eloquent\Model;
use YouTubeAutomator\Models\User;

class DescriptionChange extends Model
{
    public $guarded = [];

    /**
     * Change the video's description via the YouTube API.
     *
     * @return boolean
     */
    public function execute()
    {
        $googleClient = App::make('Google_Client');
        $user = User::find($this->user_id);
        $googleClient->setAccessToken($user->access_token);
        $youtube = new Google_Service_YouTube($googleClient);


        $listResponse = $youtube->videos->listVideos("snippet", array('id' => $this->video_id));
        if (count($listResponse) < 1 ) {
            return false;
        }

        $video = $listResponse[0];
        $videoSnippet = $video['snippet'];
        $videoSnippet['description'] = $this->description;

        $updateResponse = $youtube->videos->update("snippet", $video);

        $this->success = ($updateResponse->snippet->description === $this->description);
        $this->executed_at = (new DateTime)->format('Y-m-d H:i:s');
        $this->save();

        return $this->success;
    }
}
