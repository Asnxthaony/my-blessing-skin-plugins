<?php

return [
    App\Events\PluginWasEnabled::class => function () {
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->string('action');
                $table->text('details')->default('');
                $table->ipAddress('ip');
                $table->string('user_agent');
                $table->dateTime('created_at');

                // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->index('action', 'idx_action');
                $table->index('ip', 'idx_ip');
            });
        }
    },
];
