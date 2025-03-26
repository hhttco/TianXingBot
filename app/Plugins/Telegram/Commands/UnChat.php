<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class UnChat extends Telegram {
    public $command = '/unchat';
    public $description = '解除禁言';

    public function handle($message, $match = []) {
        $telegramService = $this->telegramService;
        $r = $telegramService->restrictChatMember(0, 0, 1, true);
        $telegramService->sendMessage($message->chat_id, json_encode($r), 'markdown');
    }
}
