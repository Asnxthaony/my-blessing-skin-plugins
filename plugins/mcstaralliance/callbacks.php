<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('connect_mcbbs')) {
            Schema::create('connect_mcbbs', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->mediumInteger('forum_uid')->unique();
                $table->char('forum_username', 15);
                $table->smallInteger('forum_groupid');
                $table->timestamps();

                // $table->foreign('user_id')->references('uid')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('connect_qq')) {
            Schema::create('connect_qq', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedBigInteger('qq_id')->unique();
                $table->dateTime('created_at');

                // $table->foreign('user_id')->references('uid')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('connect_dingtalk')) {
            Schema::create('connect_dingtalk', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('nickname');
                $table->string('open_id')->unique();
                $table->string('union_id')->unique();
                $table->timestamps();

                // $table->foreign('user_id')->references('uid')->on('users')->onDelete('cascade');
            });
        }
    },
];
