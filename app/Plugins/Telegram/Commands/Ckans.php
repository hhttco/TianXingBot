<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use Illuminate\Support\Facades\Redis;
use App\Models\TgGroupConfig;

class Ckans extends Telegram {
    public $command = '/ckans';
    public $description = '入群验证回答';

    public function handle($message, $match = []) {
        // $this->common->help($message->chat_id);
        $redisKey = 'checkJoin' . $message->chat_id . $message->user_id;
        if (!Redis::exists($redisKey)) return;

        $redisAns = Redis::get($redisKey);
        if ($redisAns == $message->check_answer) {
            // 回答正确

            // 回答正确后解除限制
            $this->telegramService->restrictChatMember($message->chat_id, $message->user_id, 1, true);

            // 删除
            $this->telegramService->deleteMessage($message->chat_id, $message->message_id);

            // 发送欢迎词
            $this->common->welcome($message->chat_id, $message->user_id, $message->user_name);
        } else {
            // 回答错误

            // 踢出群组
            $this->telegramService->banChatMember($message->chat_id, $message->user_id, time() + 90);
        }

        Redis::del($redisKey);
    }
}
