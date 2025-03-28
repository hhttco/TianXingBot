<?php

namespace App\Plugins\Telegram\Commands;

use App\Plugins\Telegram\Telegram;

class ConfigWelcome extends Telegram {
    public $command = '/configwelcome';
    public $description = '配置欢迎语开关';

    public function handle($message, $match = []) {
        if ($message->is_private) {
            abort(500, '请在群聊中设置');
        };

        $this->common->editConfig($message->chat_id, $message->message_id, $message->user_id, 'configwelcome');
    }
}
