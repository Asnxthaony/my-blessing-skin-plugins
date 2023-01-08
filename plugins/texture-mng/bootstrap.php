<?php

use App\Services\Hook;
use App\Services\Plugin;
use Blessing\Filter;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Route;

return function (Dispatcher $events, Filter $filter, Plugin $plugin) {
    $events->listen(Authenticated::class, function ($event) use ($plugin, $filter) {
        $user = $event->user;

        if ($user->isAdmin() && request()->is('skinlib/show/*')) {
            $filter->add('grid:skinlib.show', function ($grid) {
                $grid['widgets'][0][1][] = 'TextureMng::panel';

                return $grid;
            });

            Hook::addScriptFileToPage($plugin->assets('texture-mng-panel.js'));
        }
    });

    Hook::addRoute(function () {
        Route::namespace('TextureMng')
            ->middleware(['web', 'auth', 'role:admin'])
            ->prefix('admin/texture-mng')
            ->group(function () {
                Route::get('', 'TextureMngController@show');
                Route::post('handle', 'TextureMngController@handle');
            });
    });

    Hook::addMenuItem('admin', 5001, [
        'title' => '材质操作日志',
        'link' => 'admin/texture-mng',
        'icon' => 'fa-wrench',
    ]);
};
