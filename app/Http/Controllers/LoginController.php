<?php

namespace YouTubeAutomator\Http\Controllers;

use App;
use Auth;
use Google_Client;
use Google_Service_Oauth2;
use Redirect;
use Illuminate\Http\Request;
use YouTubeAutomator\Models\User;

class LoginController extends Controller
{
    /**
     * /login
     * Authenticate the user via Google OAuth then redirect to homepage.
     *
     * @param  Request       $request
     * @param  Google_Client $googleClient
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function anyIndex(Request $request, Google_Client $googleClient)
    {
        if ($code = $request->input('code')) {
            if ($request->session()->get('state') != $request->input('state')) {
                App::abort(400, 'The session state did not match.');
            }

            $googleClient->authenticate($code);

            // Now get the user and log them in to the app
            $oauth2 = new Google_Service_Oauth2($googleClient);
            $userInfo = $oauth2->userinfo->get();

            if ($user = User::where('google_user_id', $userInfo->id)->first()) {
                // User is already in DB.
            } else {
                // Create a new user.
                $user = new User([
                    'google_user_id' => $userInfo->id,
                ]);
            }

            $user->setAccessToken(
                $googleClient->getAccessToken(),
                $googleClient->getRefreshToken()
            );

            $user->name = $userInfo->name;
            $user->save();
            Auth::login($user);

            return redirect()->secure('/');
        }

        $state = (string)mt_rand();
        $googleClient->setState($state);
        $request->session()->flash('state', $state);

        $googleClient->setApprovalPrompt('force');
        $authUrl = $googleClient->createAuthUrl();
        return redirect($authUrl);
    }
}
