<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'RootController@getIndex');

Route::get('videos', 'RootController@getVideos');

Route::get('videos/{videoId}/description-changes', 'DescriptionChangesController@getDescriptionChanges');
Route::post('videos/{videoId}/description-changes', 'DescriptionChangesController@postDescriptionChanges');
Route::put('videos/{videoId}/description-changes/{descriptionChangeId}', 'DescriptionChangesController@putDescriptionChanges');
Route::delete('videos/{videoId}/description-changes/{descriptionChangeId}', 'DescriptionChangesController@deleteDescriptionChanges');

Route::controller('login', 'LoginController');
