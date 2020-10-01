<?php

use App\Events\RenderingHeader;
use App\Services\Hook;
use Blessing\Filter;
use Blessing\Rejection;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

return function (Dispatcher $events, Request $request, Filter $filter) {
    $events->listen(Illuminate\Auth\Events\Authenticated::class, function ($payload) use ($filter) {
        $filter->add('user_can_edit_profile', function ($can, $action) {
            switch ($action) {
                case 'delete':
                    return new Rejection('请使用 Telegram 联系 @Asnxthaony 申请删除您的账号。');
                default:
                    break;
            }
        });

        $filter->add('grid:user.profile', function ($grid) {
            array_unshift($grid['widgets'][0][0], 'mcstaralliance::uid');

            return $grid;
        });
    });

    $events->listen(RenderingHeader::class, function ($event) {
        $path = request()->path();
        $excludes = ['user/player', 'user/closet', 'skinlib'];
        if (!(in_array($path, $excludes) || explode('/', $path, 2)[0] == 'skinlib')) {
            $now = Carbon::now();

            // 国家公祭日
            if ($now->month == 12 && $now->day == 13) {
                $event->addContent('<style>html { filter: gray; -webkit-filter: grayscale(100%); }</style>');
            }
        }
    });

    // Live2D
    Hook::addScriptFileToPage(plugin_assets('mcstaralliance', 'js/waifu-tips.js'), ['user', 'user/*']);

    // Connect
    Hook::addScriptFileToPage(plugin_assets('mcstaralliance', 'js/mcbbs.js'), ['auth/login', 'auth/register']);

    Hook::addMenuItem('user', 1001, [
        'title' => '账号绑定',
        'icon' => 'fa-link',
        'link' => 'user/connect',
    ]);

    Hook::addMenuItem('admin', 1001, [
        'title' => '账号绑定 - MCBBS',
        'icon' => 'fa-link',
        'link' => 'admin/connect/mcbbs',
    ]);

    Hook::addMenuItem('admin', 1002, [
        'title' => '账号绑定 - QQ',
        'icon' => 'fa-link',
        'link' => 'admin/connect/qq',
    ]);

    Hook::addRoute(function () {
        Route::prefix('user/connect')
            ->middleware(['web'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('', 'ConnectController@list')->middleware(['auth']);

                Route::get('mcbbs', 'ConnectController@mcbbsLogin');
                Route::get('mcbbs/callback', 'ConnectController@mcbbsCallback');

                Route::get('qq/callback', 'ConnectController@qqCallback');
            });

        Route::prefix('admin/connect')
            ->middleware(['web', 'auth', 'role:admin'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('mcbbs', 'McbbsController@mcbbsPage');
                Route::get('qq', 'QQController@qqPage');
            });

        Route::prefix('auth/login')
            ->middleware(['web', 'guest'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('mcbbs', 'ConnectController@mcbbsLogin');
            });

        Route::prefix('api/x-auth')
            ->middleware(['api', 'throttle:60,1'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::post('qq', 'ConnectController@qqLogin');
            });
    });

    $events->listen('SocialiteProviders\Manager\SocialiteWasCalled', 'mcstaralliance\Providers\McbbsExtendSocialite@handle');

    if (($request->is('auth/login') || $request->is('auth/register')) && $request->isMethod('POST') && $request->has('provider') && $request->has('token')) {
        $events->listen('auth.login.succeeded', 'mcstaralliance\ConnectController@mcbbsNewBind');
        switch (request()->input('provider')) {
            case 'mcbbs':
                $events->listen('auth.login.succeeded', 'mcstaralliance\ConnectController@mcbbsNewBind');
                break;
            case 'qq':
                $events->listen('auth.login.succeeded', 'mcstaralliance\ConnectController@qqNewBind');
                break;
        }
    }

    config(['services.mcbbs' => [
        'client_id' => env('MCBBS_KEY'),
        'client_secret' => env('MCBBS_SECRET'),
        'redirect' => env('MCBBS_REDIRECT_URI'),
    ]]);

    config(['services.qq' => [
        'access_token' => env('QQ_ACCESS_TOKEN'),
    ]]);

    $filter->add('oauth_providers', function (Collection $providers) {
        $providers->put('mcbbs', [
            'icon' => 'cubes fas',
            'displayName' => 'MCBBS',
        ]);

        return $providers;
    });

    // Misc
    Hook::addMenuItem('explore', 1001, [
        'title' => 'mcstaralliance::menu.manual',
        'link' => '/manual',
        'icon' => 'fa-book',
        'new-tab' => true,
    ]);

    Hook::addMenuItem('explore', 1002, [
        'title' => 'mcstaralliance::menu.donate',
        'link' => 'https://afdian.net/@xiaoye',
        'icon' => 'fa-donate',
        'new-tab' => true,
    ]);
};
