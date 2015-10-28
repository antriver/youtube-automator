<?php

namespace YouTubeAutomator\Models;

use App;
use DateTime;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * YouTubeAutomator\Models\User
 *
 * @mixin \Eloquent
 * @property integer $id
 * @property string $google_user_id
 * @property string $name
 * @property string $access_token
 * @property string $refresh_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereGoogleUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereAccessToken($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereRefreshToken($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereUpdatedAt($value)
 * @property string $access_token_expires
 * @method static \Illuminate\Database\Query\Builder|\YouTubeAutomator\Models\User whereAccessTokenExpires($value)
 */
class User extends Model implements AuthenticatableContract
{
    use Authenticatable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function clearAccessToken()
    {
        $this->access_token = null;
        $this->access_token_expires = null;
        $this->refresh_token = null;
    }

    public function setAccessToken($accessToken, $refreshToken)
    {
        $token = json_decode($accessToken);
        $tokenExpires = (new DateTime)->setTimestamp($token->created + $token->expires_in)->format('Y-m-d H:i:s');

        $this->access_token = $accessToken;
        $this->access_token_expires = $tokenExpires;
        $this->refresh_token = $refreshToken;
    }

    public function refreshAccessToken()
    {
        $googleClient = App::make('Google_Client');

        $googleClient->setAccessToken($this->access_token);
        $googleClient->refreshToken($this->refresh_token);

        $this->setAccessToken(
            $googleClient->getAccessToken(),
            $googleClient->getRefreshToken()
        );
        $this->save();
    }
}
