<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class ConfigClose extends Telegram {
    public $command = '/configclose';
    public $description = '关闭配置菜单';

    public function handle($message, $match = []) {
        $this->telegramService->deleteMessage($message->chat_id, $message->message_id);
    }
}
