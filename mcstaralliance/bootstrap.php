<?php

use App\Models\User;
use App\Services\Hook;
use App\Events\RenderingHeader;
use App\Events\RenderingFooter;
use Blessing\Filter;
use Blessing\Rejection;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Filter $filter) {
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
        if (! (in_array($path, $excludes) || explode("/", $path, 2)[0] == 'skinlib')) {
            $now = Carbon::now();

            // 国家公祭日
            if ($now->month == 12 && $now->day == 13) {
                $event->addContent('<style>html { filter: gray; -webkit-filter: grayscale(100%); }</style>');
            }
        }
    });

    // Live2D
    Hook::addScriptFileToPage(plugin_assets('mcstaralliance', 'js/waifu-tips.js'), ['user', 'user/*']);

    $events->listen(RenderingFooter::class, function ($event) {
        // Goggle Analytics
        $event->addContent('<script async src="https://www.googletagmanager.com/gtag/js?id=UA-154807642-1"></script>');
    });

    Hook::addScriptFileToPage(plugin_assets('mcstaralliance', 'js/ga.js'), ['*']);

    Hook::addMenuItem('user', 1001, [
        'title' => '账号绑定',
        'icon'  => 'fa-link',
        'link' => 'user/connect',
    ]);

    Hook::addMenuItem('admin', 1001, [
        'title' => '绑 - 我的世界中文论坛',
        'icon'  => 'fa-link',
        'link' => 'admin/connect/mcbbs',
    ]);

    Hook::addRoute(function () {
        Route::prefix('user/connect')
            ->middleware(['web'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('', 'ConnectController@list')->middleware(['auth']);

                Route::get('mcbbs', 'ConnectController@mcbbsLogin');
                Route::get('mcbbs/callback', 'ConnectController@mcbbsCallback');
            });

        Route::prefix('admin/connect')
            ->middleware(['web', 'auth', 'role:admin'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('mcbbs', 'ConfigController@mcbbsPage');
            });

        Route::prefix('auth/login')
            ->middleware(['web', 'guest'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('mcbbs', 'ConnectController@mcbbsLogin');
            });
    });

    $events->listen(
        'SocialiteProviders\Manager\SocialiteWasCalled',
        'mcstaralliance\Providers\McbbsExtendSocialite@handle'
    );

    config(['services.mcbbs' => [
        'client_id' => env('MCBBS_KEY'),
        'client_secret' => env('MCBBS_SECRET'),
        'redirect' => env('MCBBS_REDIRECT_URI'),
    ]]);

    $filter->add('oauth_providers', function (Collection $providers) {
        $providers->put('mcbbs', [
            'icon' => 'cubes fas',
            'displayName' => '我的世界中文论坛',
        ]);

        return $providers;
    });

    Hook::addMenuItem('explore', 1001, [
        'title' => '用户使用手册',
        'link'  => '/manual',
        'icon'  => 'fa-book',
        'new-tab' => true,
    ]);

    Hook::addMenuItem('explore', 1002, [
        'title' => '捐助支持',
        'link'  => 'https://afdian.net/@xiaoye',
        'icon'  => 'fa-donate',
        'new-tab' => true,
    ]);
};
