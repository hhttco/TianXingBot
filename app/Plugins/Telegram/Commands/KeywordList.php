<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use App\Models\TgGroupKeyword;

class KeywordList extends Telegram {
    public $command = '/list';
    public $description = '获取关键词列表';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中获取');
        };

        // 权限验证
        if (!$this->common->power($message->chat_id, $message->user_id)) return;

        $keywords = TgGroupKeyword::where('group_id', $message->chat_id)->where('group_keyword_state', 1)->get();
        if (count($keywords) == 0) {
            return;
        }

        $list = [
            '关键词列表'
        ];

        foreach ($keywords as $k => $v) {
            $list[] = $v->group_keyword . '===' . $v->group_keyword_reply;
        }

        $text = implode(PHP_EOL, $list);
        $this->telegramService->sendMessage($message->user_id, $text, 'markdown');

        // 删除消息
        $this->telegramService->deleteMessage($message->chat_id, $message->message_id);
    }
}
