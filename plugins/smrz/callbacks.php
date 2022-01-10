<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('realname_info')) {
            Schema::create('realname_info', function ($table) {
                $table->bigIncrements('id');
                $table->bigInteger('user_id');
                $table->string('realname');
                $table->string('id_card');
                $table->tinyInteger('state');
                $table->string('reason');
                $table->timestamps();
            });
        }
    },
];
