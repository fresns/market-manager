<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run fresns migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fskey', 64)->unique('fskey');
            $table->unsignedTinyInteger('type')->default(1); // 1.Plugin 2.Theme 3.App(remote) 4.App(download)
            $table->string('name', 64);
            $table->string('description');
            $table->string('version', 16);
            $table->string('author', 64);
            $table->string('author_link', 128)->nullable();
            $table->json('panel_usages')->nullable();
            $table->string('app_host', 128)->nullable();
            $table->string('access_path')->nullable();
            $table->string('settings_path', 128)->nullable();
            $table->boolean('is_upgrade')->default(0);
            $table->string('upgrade_code', 32)->nullable();
            $table->string('upgrade_version', 16)->nullable();
            $table->boolean('is_enabled')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse fresns migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
