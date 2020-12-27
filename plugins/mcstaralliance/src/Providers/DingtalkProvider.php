<?php

namespace mcstaralliance\Providers;

use Carbon\Carbon;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class DingtalkProvider extends AbstractProvider
{
    protected $scopes = ['snsapi_login'];

    protected $openId;

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://oapi.dingtalk.com/connect/qrconnect', $state);
    }

    protected function getTokenUrl()
    {
        return null;
    }

    protected function getUserByToken($token)
    {
        $timestamp = Carbon::now()->getPreciseTimestamp(3);
        $signature = $this->generateSignature($timestamp, $this->clientSecret);

        $response = $this->getHttpClient()->post('https://oapi.dingtalk.com/sns/getuserinfo_bycode', [
            'query' => [
                'accessKey' => $this->clientId,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
            'json' => [
                'tmp_auth_code' => $token,
            ],
        ]);

        $user = json_decode($response->getBody(), true);

        if (isset($user['errcode']) && $user['errcode'] != 0) {
            abort(500, $user['errmsg']);
        }

        return $user['user_info'];
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'nickname' => $user['nick'],
            'open_id' => $user['openid'],
            'union_id' => $user['unionid'],
        ]);
    }

    public function getAccessTokenResponse($code)
    {
        return ['access_token' => $code];
    }

    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);
        unset($fields['client_id']);
        $fields['appid'] = $this->clientId;

        return $fields;
    }

    protected function generateSignature($timestamp, $appSecret)
    {
        $sig = hash_hmac('sha256', $timestamp, $appSecret, true);

        return base64_encode($sig);
    }
}
