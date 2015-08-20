<?php

namespace YouTubeAutomator\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use YouTubeAutomator\Models\DescriptionChange;
use YouTubeAutomator\Models\YouTube\Video;

class ExecuteChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'executechanges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run description changes';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $changes = $this->getScheduledChanges();

        foreach ($changes as $change) {
            $change->execute();
        }
    }

    /**
     * Get changes that should be run now.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getScheduledChanges()
    {
        $now = (new DateTime)->format('Y-m-d H:i:s');

        return DescriptionChange::where('execute_at', '<=', $now)
            ->whereNull('executed_at');
    }

    /**
     * Get all the queued changes that are for unpublished videos.
     * If the video has become published set the correct time to execute based
     * on the videos' publish time.
     *
     * @return null
     */
    public function checkForPublishedVideos()
    {
        $pendingVideoChanges = DescriptionChange::whereNotNull('execute_mins_after_publish')
            ->whereNull('executed_at');

        foreach ($pendingVideoChanges as $pendingVideoChange) {
            // TODO: Cache video
            $video = $pendingVideoChange->getVideo();

            echo "{$pendingVideoChange->id}";

            if ($video->isPublished()) {
                $publishedAt = strtotime($video->getPublishedDate());
                $executeAt = $publishedAt + $pendingVideoChange->execute_mins_after_publish * 60;
                $pendingVideoChange->execute_at = date('Y-m-d H:i:s', $executeAt);
                $pendingVideoChange->execute_mins_after_publish = null;
                $pendingVideoChange->save();
            }

        }
    }
}
