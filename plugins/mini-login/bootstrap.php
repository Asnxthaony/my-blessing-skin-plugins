<?php

use App\Services\Hook;
use Blessing\Filter;

return function (Filter $filter) {
    Hook::addScriptFileToPage(plugin('mini-login')->assets('js/mini-login.js'), ['auth/login', 'mini-login/list']);

    Hook::addRoute(function () {
        Route::namespace('Asnxthaony\MiniLogin\Controllers')
            ->middleware(['web', 'auth'])
            ->prefix('mini-login')
            ->group(function () {
                Route::get('list', 'MiniLoginController@list');
                Route::get('wechat/bind', 'MiniLoginController@wechatBind');
            });

        Route::namespace('Asnxthaony\MiniLogin\Controllers')
            ->middleware(['web', 'guest'])
            ->prefix('mini-login')
            ->group(function () {
                Route::get('wechat/login', 'MiniLoginController@wechatLogin');
                Route::post('wechat/login/check', 'MiniLoginController@wechatLoginCheck');
            });

        Route::namespace('Asnxthaony\MiniLogin\Controllers')
            ->middleware(['api'])
            ->prefix('mini-login')
            ->group(function () {
                Route::post('wechat/callback', 'MiniLoginController@wechatCallback');
            });
    });

    $filter->add('auth_page_rows:login', function ($rows) {
        $length = count($rows);
        array_splice($rows, $length - 1, 0, ['Asnxthaony\MiniLogin::wechat-login']);

        return $rows;
    });

    config(['services.wechat' => [
        'app_id' => env('WECHAT_APPID'),
        'secret' => env('WECHAT_SECRET'),
    ]]);

    Hook::addMenuItem('user', 3001, [
        'title' => '星登录',
        'link' => 'mini-login/list',
        'icon' => 'fa-shield-alt',
    ]);
};
