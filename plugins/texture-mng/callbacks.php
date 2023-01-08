<?php

use Illuminate\Support\Facades\Schema;

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('texture_mng_record')) {
            Schema::create('texture_mng_record', function ($table) {
                $table->bigIncrements('id'); // ID
                $table->bigInteger('user_id'); // UID
                $table->bigInteger('texture_id'); // 材质ID
                $table->bigInteger('operator'); // 操作人
                $table->string('reason'); // 理由
                $table->dateTime('created_at'); // 创建时间
            });
        }
    },
];
