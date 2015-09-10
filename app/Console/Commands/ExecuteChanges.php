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

        $this->log->info("Executing changes...");

        $this->checkForPublishedVideos();

        $changes = $this->getScheduledChanges();

        foreach ($changes as $change) {
            $this->log->info("Executing change {$change->id}");
            $success = $change->execute();
            $this->log->info("Change {$change->id} result: {$success}");
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
        $pendingVideoChanges = DescriptionChange::whereNotNull('execute_mins_after_publish')
            ->whereNull('executed_at')
            ->groupBy('video_id')
            ->get();

        foreach ($pendingVideoChanges as $pendingVideoChange) {
            $this->log->info("{$pendingVideoChange->id} {$pendingVideoChange->video_id}");

            // We need to login as the user that owns this video in order to query it

            $user = User::find($pendingVideoChange->user_id);
            if (!$user) {
                $this->log->error("User {$pendingVideoChange->user_id} not found.");
                continue;
            }

            $googleClient = App::make('Google_Client');
            $googleClient->setAccessToken($user->access_token);

            $video = $pendingVideoChange->getVideo();

            if ($video->isPublished()) {
                $this->log->info("Video {$pendingVideoChange->video_id} published at "
                    . date('r', $video->getPublishedTimestamp()));

                $thisVideoChanges = DescriptionChange::where('video_id', $pendingVideoChange->video_id)
                    ->whereNotNull('execute_mins_after_publish')
                    ->whereNull('executed_at')
                    ->get();

                foreach ($thisVideoChanges as $thisVideoChange) {
                    $publishedAt = $video->getPublishedTimestamp();
                    $executeAt = $publishedAt + $thisVideoChange->execute_mins_after_publish * 60;

                    $this->log->info("Setting execute time for change {$thisVideoChange->id} to "
                        . date('r', $executeAt));

                    $thisVideoChange->execute_at = date('Y-m-d H:i:s', $executeAt);
                    $thisVideoChange->execute_mins_after_publish = null;
                    $thisVideoChange->save();
                }
            } else {
                $this->log->notice("Video {$pendingVideoChange->video_id} not published.");
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
