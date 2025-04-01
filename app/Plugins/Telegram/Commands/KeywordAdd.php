<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use App\Models\TgGroupKeyword;

class KeywordAdd extends Telegram {
    public $command = '/add';
    public $description = '添加关键词';

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

        // 例子：优惠码===abc111
        $arrKeyword = explode('===', $message->args[0]);
        if (count($arrKeyword) < 2) {
            abort(500, '输入参数格式错误');
        }

        // 查询数据库是否存在
        $groupKeyword = TgGroupKeyword::where('group_id', $message->chat_id)->where('group_keyword', $arrKeyword[0])->first();
        if (!$groupKeyword) {
            // 不存在插入
            $groupKeyword = new TgGroupKeyword;
            $groupKeyword->group_id      = $message->chat_id;
            $groupKeyword->group_keyword = $arrKeyword[0];
        }

        // 存在就更新
        $groupKeyword->create_user_id = $message->user_id;
        $groupKeyword->create_user_name = $message->user_name;
        $groupKeyword->group_keyword_reply = $arrKeyword[1];
        $groupKeyword->group_keyword_state = 1;
        $groupKeyword->save();

        // 回复设置的消息
        $this->telegramService->sendMessage($message->chat_id, $groupKeyword->group_keyword_reply, 'markdown', $message->message_id);
    }
}
