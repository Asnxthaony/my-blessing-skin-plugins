<?php

use Carbon\Carbon;

use App\Models\User;
use App\Services\Hook;
use App\Events\RenderingHeader;
use App\Events\RenderingFooter;
use Blessing\Filter;
use Blessing\Rejection;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Filter $filter) {
    $events->listen(Illuminate\Auth\Events\Authenticated::class, function ($payload) use ($filter) {
        $now = Carbon::now();
        switch ($now->month) {
            case '5':
                switch ($now->day) {
                    case '35':
                        Hook::addUserBadge('Impossible', 'black');
                        break;
                }
                break;
        }

        $filter->add('user_can_edit_profile', function ($can, $action, $addition) {
            switch ($action) {
                case 'delete':
                    return new Rejection('请使用 Telegram 联系 @Asnxthaony 申请删除您的账号。');
                default:
                    break;
            }
        });

        $filter->add('user_can_rename_player', function ($can, $player, $newName) {
            return new Rejection('请使用 Telegram 联系 @Asnxthaony 申请更改您的角色名。');
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
            if ($now->year == 2020 && $now->month == 4 && $now->day == 4) {
                $event->addContent('<style>html { filter: gray; -webkit-filter: grayscale(100%); }</style>');
            }
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

    Hook::addRoute(function () {
        Route::prefix('user/connect')
            ->middleware(['web', 'auth'])
            ->namespace('mcstaralliance')
            ->group(function () {
                Route::get('', 'ConnectController@list');
                Route::get('mcbbs', 'ConnectController@mcbbs_connect');
                Route::get('mcbbs/callback', 'ConnectController@handleMcbbsCallback');
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
