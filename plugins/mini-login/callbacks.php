<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('wechat_users')) {
            Schema::create('wechat_users', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('open_id')->unique();
                // $table->string('union_id')->unique();
                $table->timestamps();
            });
        }
    },
];
