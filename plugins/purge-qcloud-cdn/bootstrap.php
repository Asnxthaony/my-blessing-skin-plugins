<?php

use Asnxthaony\PurgeQcloudCdn\PurgeCDN;
use Illuminate\Contracts\Events\Dispatcher;

return function (Dispatcher $events) {
    config(['logging.channels.purge-qcloud-cdn' => [
        'driver' => 'single',
        'path' => storage_path('logs/purge-qcloud-cdn.log'),
    ]]);

    $events->listen(App\Events\PlayerProfileUpdated::class, function ($event) {
        PurgeCDN::dispatch($event->player);
    });
};
