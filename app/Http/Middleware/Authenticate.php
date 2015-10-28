<?php

namespace YouTubeAutomator\Http\Middleware;

use App;
use Auth;
use Closure;
use Google_Client;
use Illuminate\Contracts\Auth\Guard;
use YouTubeAutomator\Models\User;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var User $user */
        $user = $this->auth->user();

        if ($user) {
            $googleClient = App::make('Google_Client');

            if ($user->access_token) {
                $googleClient->setAccessToken($user->access_token);

                if ($googleClient->isAccessTokenExpired()) {
                    $user->refreshAccessToken();
                }

                return $next($request);
            }

        }

        return redirect()->secure('/login');
    }
}
