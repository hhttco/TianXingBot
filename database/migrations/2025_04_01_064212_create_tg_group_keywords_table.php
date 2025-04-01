<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTgGroupKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tg_group_keywords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group_id', 100);
            $table->string('group_keyword', 100); // 关键词
            $table->string('group_keyword_reply', 100); // 关键词回复内容
            $table->tinyInteger('group_keyword_state')->default(0); // 关键词状态 0 禁用 1 启用
            $table->string('create_user_id', 100); // 添加或者最后更新人
            $table->string('create_user_name', 100);
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
        Schema::dropIfExists('tg_group_keywords');
    }
}
