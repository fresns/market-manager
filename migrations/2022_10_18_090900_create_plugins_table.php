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
        Schema::create('plugins', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fskey', 64)->unique('plugin_fskey');
            $table->unsignedTinyInteger('type')->default(1);
            $table->string('name', 64);
            $table->string('description');
            $table->string('version', 16);
            $table->string('author', 64);
            $table->string('author_link', 128)->nullable();
            switch (config('database.default')) {
                case 'pgsql':
                    $table->jsonb('scene')->nullable();
                    break;

                case 'sqlsrv':
                    $table->nvarchar('scene', 'max')->nullable();
                    break;

                default:
                    $table->json('scene')->nullable();
            }
            $table->string('plugin_host', 128)->nullable();
            $table->string('access_path')->nullable();
            $table->string('settings_path', 128)->nullable();
            $table->unsignedTinyInteger('is_standalone')->default(0);
            $table->unsignedTinyInteger('is_upgrade')->default(0);
            $table->string('upgrade_code', 32)->nullable();
            $table->string('upgrade_version', 16)->nullable();
            $table->unsignedTinyInteger('is_enabled')->default(0);
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
        Schema::dropIfExists('plugins');
    }
};
