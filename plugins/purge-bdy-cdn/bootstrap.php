<?php

use Asnxthaony\PurgeBdyCdn\PurgeCDN;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    config(['logging.channels.purge-bdy-cdn' => [
        'driver' => 'single',
        'path' => storage_path('logs/purge-bdy-cdn.log'),
    ]]);

    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        PurgeCDN::dispatch($event->player);
    });
};
