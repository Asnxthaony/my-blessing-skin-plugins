<?php

namespace mcstaralliance\Providers;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class McbbsProvider extends AbstractProvider
{
    protected $openId;

    protected function getAuthUrl($state)
    {
        return 'https://www.mcbbs.net/plugin.php?id=mcbbs_api:oauth2&'.http_build_query($this->getCodeFields($state), '', '&');
    }

    protected function getTokenUrl()
    {
        return 'https://www.mcbbs.net/plugin.php?id=mcbbs_api:token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://www.mcbbs.net/plugin.php', [
            'query' => [
                'id' => 'mcbbs_api:api',
                'module' => 'user:get_user_info',
                'oauth_consumer_key' => $this->clientId,
                'openid' => $this->openId,
                'access_token' => $token,
            ],
        ]);

        $user = json_decode($response->getBody(), true);

        if (isset($user['error_code'])) {
            abort(500, $user['error_description']);
        }

        return $user;
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['uid'],
            'nickname' => $user['username'],
            'avatar' => $user['avatar'],
            'groupid' => $user['groupid'],
        ]);
    }

    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        $data = json_decode($response->getBody(), true);
        $this->openId = $data['uid'];

        return $data;
    }

    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
