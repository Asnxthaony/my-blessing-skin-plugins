<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (! Schema::hasTable('connect_mcbbs')) {
            Schema::create('connect_mcbbs', function ($table) {
                $table->increments('id');
                $table->integer('user_id')->unique();
                $table->mediumInteger('forum_uid')->unique();
                $table->char('forum_username', 50);
                $table->smallInteger('forum_groupid');
                $table->timestamps();
            });
        }
    },
];
