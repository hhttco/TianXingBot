<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;
use Illuminate\Support\Facades\Log;

class Ckans extends Telegram {
    public $command = '/ckans';
    public $description = '入群验证回答';

    public function handle($message, $match = []) {
        $this->common->help($message->chat_id);

        Log::info('获取的答案是=>>>>>' . $message->check_answer);
        Log::info('当前时间戳=>>>>>' . time());
        Log::info('当前时间戳加上90=>>>>>' . (time() + 90));

        Log::info('数据是' . $message->chat_id . ' -- ' . $message->user_id);

        // 回答正确后解除限制
        $this->telegramService->restrictChatMember($message->chat_id, $message->user_id, 1, true);

        // 删除
        $this->telegramService->deleteMessage($message->chat_id, $message->message_id);
    }
}
