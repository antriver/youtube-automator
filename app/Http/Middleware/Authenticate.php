<?php

namespace YoutubeAutomator\Http\Middleware;

use App;
use Auth;
use Closure;
use Google_Client;
use Illuminate\Contracts\Auth\Guard;

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
     * @return void
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
        if ($this->auth->user()) {
            $googleClient = App::make('Google_Client');
            $googleClient->setAccessToken($this->auth->user()->access_token);
        } else {
            return redirect('/login');
        }

        return $next($request);
    }
}
