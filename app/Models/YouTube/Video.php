<?php

namespace YouTubeAutomator\Models\YouTube;

use App;
use Google_Service_YouTube;
use YouTubeAutomator\Models\DescriptionChange;

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

    /**
     * Get all DescriptionChanges related to this video.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getDescriptionChanges()
    {
        return DescriptionChange::where('video_id', $this->videoId)
            ->orderBy('executed_at')
            ->orderBy('execute_at')
            ->get();
    }

    /**
     * Return the underlying data for the video.
     *
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return the video title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->data->snippet->title;
    }

    /**
     * Return the video description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->data->snippet->description;
    }

    /**
     * Return the URL to the thumbnail for the video.
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->data->snippet->thumbnails->default->url;
    }

    /**
     * Check if the video is published (if its privacy is set to public).
     *
     * @return boolean
     */
    public function isPublished()
    {
        return $this->data->status->privacyStatus === 'public';
    }

    /**
     * Returns the time the video was published (uploaded or made public).
     *
     * @return string
     */
    public function getPublishedDate()
    {
// TODO: Is this right?
        return $this->data->snippet->publishedAt;
    }

    /**
     * Re-hydrate the model and return it.
     *
     * @return Video
     */
    public function fresh()
    {
        $this->hydrate();
        return $this;
    }

    /**
     * Load the data for this video ID from the YouTube API.
     *
     * @return null
     */
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
