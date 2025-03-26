<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class Help extends Telegram {
    public $command = '/help';
    public $description = '获取帮助信息';

    public function handle($message, $match = []) {
        // if (!$message->is_private) return;

        $this->common->help($message->chat_id);
    }
}
