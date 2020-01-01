<?php

use App\Services\Hook;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events, Filter $filter) {
    $events->listen(Illuminate\Auth\Events\Authenticated::class, function ($payload) use ($filter) {
        $filter->add('grid:user.index', function ($grid) {
            array_push($grid['widgets'][0][1], 'Asnxthaony\PlayerList::playerList');
            return $grid;
        });
    });

    Hook::addScriptFileToPage(plugin_assets('player-list', 'js/player-list.js'), ['user']);
};
