<?php

namespace YouTubeAutomator\Models;

use App;
use DateTime;
use Google_Service_YouTube;
use Illuminate\Database\Eloquent\Model;
use YouTubeAutomator\Models\User;
use YouTubeAutomator\Models\YouTube\Video;

/**
 * YouTubeAutomator\Models\DescriptionChange
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property integer $user_id
 * @property string $video_id
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $execute_at
 * @property integer $execute_mins_after_publish
 * @property string $executed_at
 * @property boolean $success
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereVideoId($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereExecuteAt($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereExecuteMinsAfterPublish($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereExecutedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\DescriptionChange whereSuccess($value)
 */
class DescriptionChange extends Model
{
    public $guarded = [];

    /**
     * Return the video model for this change.
     *
     * @return Video
     */
    public function getVideo()
    {
        return new Video($this->video_id);
    }

    public function getUser()
    {
        return User::find($this->user_id);
    }

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
        if (count($listResponse) < 1) {
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
