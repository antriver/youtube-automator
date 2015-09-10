<?php

namespace YouTubeAutomator\Providers;

use Config;
use Google_Client;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('Google_Client', function ($app) {

            $client = new Google_Client();
            $client->setClientId(Config::get('services.google.client_id'));
            $client->setClientSecret(Config::get('services.google.client_secret'));
            $client->setScopes([
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/youtube'
            ]);

            $redirect = Config::get('app.url') . '/login';
            $client->setRedirectUri($redirect);
            $client->setAccessType('offline');

            return $client;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
