<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTgGroupConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tg_group_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group_id', 100);
            $table->longText('group_welcome'); // 群欢迎
            $table->tinyInteger('group_welcome_state')->default(0); // 默认 0 关闭 1 启用
            $table->tinyInteger('group_join_check')->default(0); // 入群验证 默认 0 关闭 1 启用
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tg_group_config');
    }
}
