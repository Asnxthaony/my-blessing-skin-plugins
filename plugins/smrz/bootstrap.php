<?php

use App\Services\Hook;
use Asnxthaony\Smrz\Twig\Extension\DecryptExtension;
use TwigBridge\Facade\Twig;

return function () {
    Twig::addExtension(new DecryptExtension());

    Hook::addScriptFileToPage(plugin_assets('smrz', 'js/smrz.js'), ['admin/smrz']);

    Hook::addRoute(function () {
        Route::namespace('Asnxthaony\Smrz')
            ->middleware(['web', 'auth'])
            ->prefix('user/smrz')
            ->group(function () {
                Route::get('', 'RealUserController@show');
                Route::post('handle', 'RealUserController@handle');
            });

        Route::namespace('Asnxthaony\Smrz')
            ->middleware(['web', 'auth', 'role:admin'])
            ->prefix('admin/smrz')
            ->group(function () {
                Route::get('', 'RealUserManagementController@show');
            });
    });

    Hook::addMenuItem('user', 3001, [
        'title' => '实名认证',
        'link' => 'user/smrz',
        'icon' => 'fa-id-card',
    ]);

    Hook::addMenuItem('admin', 3001, [
        'title' => '实名认证',
        'link' => 'admin/smrz',
        'icon' => 'fa-id-card',
    ]);
};
