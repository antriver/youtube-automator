<?php

namespace YouTubeAutomator\Console\Commands;

use App;
use DateTime;
use Log;
use Illuminate\Console\Command;
use YouTubeAutomator\Models\DescriptionChange;
use YouTubeAutomator\Models\User;
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
     * @var Monolog
     */
    private $log;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->log = $this->getLogger();

        $this->log->info("Checking for changes to execute...");

        $this->checkForPublishedVideos();

        $changes = $this->getScheduledChanges();

        foreach ($changes as $change) {

            $user = $change->getUser();

            $this->log->info("Change {$change->id}: Executing change for video {$change->video_id} (user {$user->name})");
            try {
                $success = $change->execute();

                $snippet = substr($change->description, 0, 35) . '...';

                $this->log->info("Change {$change->id}: Successfully changed description of video {$change->video_id} to \"{$snippet}\"");

            } catch (Exception $e) {
                $this->log->error("Change {$change->id}: Exception changing video description: " . $e->getMessage());
            }
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
            ->whereNull('executed_at')
            ->get();
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
        // First get all videos (one result per video) where its excute time has not yet been set
        $pendingVideos = DescriptionChange::whereNotNull('execute_mins_after_publish')
            ->whereNull('executed_at')
            ->groupBy('video_id')
            ->get();

        foreach ($pendingVideos as $pendingVideo) {

            $user = $pendingVideo->getUser();
            if (!$user) {
                $this->log->error("User {$pendingVideo->user_id} not found in database.");
                continue;
            }

            $this->log->info("Checking if video {$pendingVideo->video_id} (user {$user->name}) is published yet.");


            // We need to login as the user that owns this video in order to query it
            try {
                $googleClient = App::make('Google_Client');
                $googleClient->setAccessToken($user->access_token);
            } catch (Exception $e) {
                $this->log->error("Exception setting Google access token: " . $e->getMessage());
                continue;
            }

            $video = $pendingVideo->getVideo();

            if ($video->isPublished()) {
                $this->log->info("Video {$pendingVideo->video_id} was published at "
                    . date('Y-m-d H:i:s', $video->getPublishedTimestamp()));

                $thisVideoChanges = DescriptionChange::where('video_id', $pendingVideo->video_id)
                    ->whereNotNull('execute_mins_after_publish')
                    ->whereNull('executed_at')
                    ->get();

                foreach ($thisVideoChanges as $thisVideoChange) {
                    $publishedAt = $video->getPublishedTimestamp();
                    $executeAt = $publishedAt + $thisVideoChange->execute_mins_after_publish * 60;
                    $thisVideoChange->execute_at = date('Y-m-d H:i:s', $executeAt);
                    $thisVideoChange->execute_mins_after_publish = null;
                    $thisVideoChange->save();

                    $this->log->info("Set execute time for change {$thisVideoChange->id} to "
                        . $thisVideoChange->execute_at);
                }
            } else {
                $this->log->notice("Video {$pendingVideo->video_id} is not published.");
            }

        }

    }

    /**
     * @return Monolog
     */
    private function getLogger()
    {
        $jobLogger = new \Monolog\Logger('Commands');
        $fileHandler = new \Monolog\Handler\RotatingFileHandler(storage_path() . '/logs/commands.log');
        $lineFormatter = new \Monolog\Formatter\LineFormatter(
            "[%datetime%] %message% %context% %extra%\n",
            null,
            true,
            true
        );
        $fileHandler->setFormatter($lineFormatter);
        $jobLogger->pushHandler($fileHandler);
        return $jobLogger;
    }
}
