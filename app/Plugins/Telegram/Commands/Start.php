<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class Start extends Telegram {
    public $command = '/start';
    public $description = '开始';

    public function handle($message, $match = []) {
        if (!$message->is_private) return;

        $this->common->help($message->chat_id);
    }
}
