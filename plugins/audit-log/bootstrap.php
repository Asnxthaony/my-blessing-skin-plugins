<?php

use App\Services\Hook;
use AuditLog\Listeners\OnForgotSent;
use AuditLog\Listeners\OnProfileUpdated;
use AuditLog\Listeners\OnUserDeleted;
use AuditLog\Listeners\OnUserLoginFailed;
use AuditLog\Listeners\OnUserLoginSucceeded;
use AuditLog\Listeners\Yggdrasil\OnAuthenticateFailed;
use AuditLog\Listeners\Yggdrasil\OnAuthenticateSucceeded;
use AuditLog\Twig\Extension\AgentExtension;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use TwigBridge\Facade\Twig;

require __DIR__.'/src/Utils/helpers.php';

return function (Dispatcher $events, Request $request) {
    Hook::addMenuItem('user', 2001, [
        'title' => 'AuditLog::log.title',
        'icon' => 'fa-history',
        'link' => 'user/audit-log',
    ]);

    Hook::addMenuItem('admin', 2001, [
        'title' => 'AuditLog::log.title',
        'icon' => 'fa-history',
        'link' => 'admin/audit-log',
    ]);

    $events->listen('auth.forgot.failed', OnForgotSent::class);
    $events->listen('user.profile.updated', OnProfileUpdated::class);
    $events->listen('user.deleted', OnUserDeleted::class);
    $events->listen('auth.login.failed', OnUserLoginFailed::class);
    $events->listen('auth.login.succeeded', OnUserLoginSucceeded::class);

    /*
     * Yggdrasil API
     */
    $events->listen('yggdrasil.authenticate.failed', OnAuthenticateFailed::class);
    $events->listen('yggdrasil.authenticate.succeeded', OnAuthenticateSucceeded::class);

    Twig::addExtension(new AgentExtension());

    Hook::addRoute(function () {
        Route::namespace('AuditLog\Controllers')
            ->middleware(['web', 'auth'])
            ->group(function () {
                Route::get('user/audit-log', 'AuditLogController@logPage');
                Route::get('admin/audit-log', 'AuditLogController@adminLogPage')->middleware('role:super-admin');
            });
    });
};
