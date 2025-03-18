<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTgGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tg_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group_id', 100);
            $table->string('group_name', 100);
            $table->bigInteger('group_user_num')->default(0); // 群组人数
            $table->string('create_user_id', 100); // 将机器人邀请入群组的人
            $table->string('create_user_name', 100);
            $table->tinyInteger('group_bot_state')->default(0); // 默认 0 已离开群组 1 在群组中
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
        Schema::dropIfExists('tg_groups');
    }
}
