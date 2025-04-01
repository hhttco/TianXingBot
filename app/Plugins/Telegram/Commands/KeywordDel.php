<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use App\Models\TgGroupKeyword;

class KeywordDel extends Telegram {
    public $command = '/adddel';
    public $description = '删除关键词';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中设置');
        };

        // 权限验证
        if (!$this->common->power($message->chat_id, $message->user_id)) {
            abort(500, '请联系管理员操作');
        }

        if (!isset($message->args[0])) {
            abort(500, '参数有误！如需要关闭欢迎词请前往配置项配置');
        }

        // 例子：优惠码

        // 查询数据库是否存在
        $groupKeyword = TgGroupKeyword::where('group_id', $message->chat_id)->where('group_keyword', $message->args[0])->first();
        if (!$groupKeyword) {
            // 不存在插入
            abort(500, '关键词不存在');
        }

        // 存在就更新
        $groupKeyword->create_user_id = $message->user_id;
        $groupKeyword->create_user_name = $message->user_name;
        $groupKeyword->group_keyword_state = 0;
        $groupKeyword->save();

        // 回复设置的消息
        $this->telegramService->sendMessage($message->chat_id, '关键词删除成功', 'markdown', $message->message_id);
    }
}
