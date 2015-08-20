<?php

namespace YouTubeAutomator\Http\Controllers;

use Auth;
use Response;
use Illuminate\Http\Request;
use YouTubeAutomator\Models\DescriptionChange;

class DescriptionChangesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Run validators on input
     *
     * @param  Request $request
     * @return boolean
     */
    private function validateInput(Request $request)
    {
        return $this->validate($request, [
            'description' => 'required',
            'execute_at' => 'date_format:"Y-m-d H:i:s"',
            'execute_mins_after_publish' => 'integer'
        ]);
    }

    /**
     * Get the specific change from the database.
     *
     * @param  string $videoId
     * @param  integer $descriptionChangeId
     * @return DescriptionChange
     */
    private function loadDescriptionChange($videoId, $descriptionChangeId)
    {
        $change = DescriptionChange::findOrFail($descriptionChangeId);
        if ($change->user_id != Auth::user()->id) {
            App::abort(403);
        }
        if ($change->video_id != $videoId) {
            App::abort(422);
        }
        return $change;
    }

    /**
     * GET /video/{videoId}/description-changes
     * Get pending description changes for a video.
     *
     * @param  string  $video_id
     * @param  Request $request
     * @return Response
     */
    public function getDescriptionChanges($videoId)
    {
        $descriptionChanges = DescriptionChange::where('video_id', $videoId)
            ->where('user_id', Auth::user()->id)
            ->get();

        return view('partials.description-changes', [
            'descriptionChanges' => $descriptionChanges
        ]);
    }

    /**
     * POST /video/{videoId}/description-changes
     * Create a new description change for a video.
     *
     * @param  string  $video_id
     * @param  Request $request
     * @return Response
     */
    public function postDescriptionChanges($videoId, Request $request)
    {
        $this->validateInput($request);

        $change = new DescriptionChange([
            'user_id' => Auth::user()->id,
            'video_id' => $videoId,
            'description' => $request->input('description'),
            'execute_at' => $request->input('execute_at') ? $request->input('execute_at') : null,
            'execute_mins_after_publish' =>
                $request->input('execute_mins_after_publish') ? $request->input('execute_mins_after_publish') : null
        ]);

        $success = $change->save();
        return Response::json(['success' => $success, 'change' => $change->fresh()]);
    }

    /**
     * PUT/PATCH /video/{videoId}/description-changes/{descriptionChangeId}
     * Update a description change.
     *
     * @param  string  $videoId
     * @param  integer  $descriptionChangeId
     * @param  Request $request
     * @return Response
     */
    public function putDescriptionChanges($videoId, $descriptionChangeId, Request $request)
    {
        $this->validateInput($request);
        $change = $this->loadDescriptionChange($videoId, $descriptionChangeId);

        $change->description = $request->input('description');
        $change->execute_at = $request->input('execute_at') ? $request->input('execute_at') : null;
        $change->execute_mins_after_publish =
            $request->input('execute_mins_after_publish') ? $request->input('execute_mins_after_publish') : null;

        $success = $change->save();
        return Response::json(['success' => $success, 'change' => $change->fresh()]);
    }

    /**
     * DELETE /video/{videoId}/description-changes/{descriptionChangeId}
     * Delete a description change.
     *
     * @param  string  $videoId
     * @param  integer  $descriptionChangeId
     * @param  Request $request
     * @return Response
     */
    public function deleteDescriptionChanges($videoId, $descriptionChangeId)
    {
        $change = $this->loadDescriptionChange($videoId, $descriptionChangeId);

        $success = $change->delete();
        return Response::json(['success' => $success]);
    }
}
